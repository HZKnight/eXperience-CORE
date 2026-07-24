<?php

namespace Experience\Tests\Core\Tools\Config;

use PHPUnit\Framework\TestCase;
use Experience\Core\Tools\Config\EConfigManager;
use Experience\Core\Io\Storage\EStorage;
use Experience\Core\Exceptions\EException;

class EConfigManagerTest extends TestCase
{
    private $storageMock;
    private string $testConfigFile = 'config.json';
    private array $sampleJsonData;

    protected function setUp(): void
    {
        parent::setUp();

        // Creamo il mock di EStorage
        $this->storageMock = $this->createMock(EStorage::class);

        // Dati JSON di esempio per i test (chiavi di primo e secondo livello)
        $this->sampleJsonData = [
            'app_name' => 'eXperience Core',
            'db' => [
                'driver'    => 'pdo_sqlite',
                'tb_prefix' => 'exp_'
            ]
        ];
    }

    /**
     * Helper per configurare il Mock di EStorage affinché simuli un file JSON valido.
     */
    private function setupValidStorageMock(array $data = null): void
    {
        $jsonData = json_encode($data ?? $this->sampleJsonData);

        $this->storageMock->method('fileExists')
            ->with($this->testConfigFile)
            ->willReturn(true);

        $this->storageMock->method('fileRead')
            ->with($this->testConfigFile)
            ->willReturn($jsonData);
    }

    /**
     * Test del caricamento e parsing corretto del file di configurazione JSON.
     */
    public function testConstructorParsesConfigCorrectly(): void
    {
        $this->setupValidStorageMock();

        $config = new EConfigManager($this->testConfigFile, $this->storageMock);

        // Verifica getCfg()
        $this->assertEquals($this->sampleJsonData, $config->getCfg());

        // Verifica lettura parametri semplici e nidificati (dot-notation)
        $this->assertTrue($config->has('app_name'));
        $this->assertEquals('eXperience Core', $config->getParam('app_name'));

        $this->assertTrue($config->has('db.driver'));
        $this->assertEquals('pdo_sqlite', $config->getParam('db.driver'));
        $this->assertEquals('exp_', $config->getParam('db.tb_prefix'));
    }

    /**
     * Test del valore di default quando un parametro non esiste.
     */
    public function testGetParamReturnsDefaultWhenNotFound(): void
    {
        $this->setupValidStorageMock();

        $config = new EConfigManager($this->testConfigFile, $this->storageMock);

        $this->assertFalse($config->has('non_existing_param'));
        $this->assertEquals('default_val', $config->getParam('non_existing_param', 'default_val'));
        $this->assertNull($config->getParam('non_existing_param'));
    }

    /**
     * Test aggiornamento di una voce di secondo livello (es: setParam('db.driver', 'mysqli')).
     */
    public function testSetParamUpdatesNestedValueAndSaves(): void
    {
        $this->setupValidStorageMock();

        // Ci aspettiamo che fileWrite venga chiamato con il JSON aggiornato
        $this->storageMock->expects($this->once())
            ->method('fileWrite')
            ->with(
                $this->testConfigFile,
                $this->callback(function ($jsonContent) {
                    $decoded = json_decode($jsonContent, true);
                    return isset($decoded['db']['driver']) && $decoded['db']['driver'] === 'mysqli';
                }),
                'w'
            )
            ->willReturn(true);

        $config = new EConfigManager($this->testConfigFile, $this->storageMock);

        // Aggiorna il valore mediante due argomenti (param.subkey, val)
        $config->setParam('db.driver', 'mysqli');

        $this->assertEquals('mysqli', $config->getParam('db.driver'));
    }

    /**
     * Test aggiornamento con tre argomenti (section, param, val).
     */
    public function testSetParamWithThreeArguments(): void
    {
        $this->setupValidStorageMock();

        $this->storageMock->expects($this->once())
            ->method('fileWrite')
            ->willReturn(true);

        $config = new EConfigManager($this->testConfigFile, $this->storageMock);

        // Chiamata con 3 argomenti: setParam('db', 'host', 'localhost')
        $config->setParam('db', 'host', 'localhost');

        $this->assertEquals('localhost', $config->getParam('db.host'));
    }

    /**
     * Test eccezione se EStorage passato è nullo (ConfigInvalidStorageException).
     */
    public function testConstructorThrowsExceptionWhenStorageIsNull(): void
    {
        $this->expectException(EException::class);

        new EConfigManager($this->testConfigFile, null);
    }

    /**
     * Test eccezione se il file di configurazione non esiste (ConfigFileNotExistException).
     */
    public function testConstructorThrowsExceptionWhenFileDoesNotExist(): void
    {
        $this->storageMock->method('fileExists')
            ->with($this->testConfigFile)
            ->willReturn(false);

        $this->expectException(EException::class);

        new EConfigManager($this->testConfigFile, $this->storageMock);
    }

    /**
     * Test eccezione se il file contiene JSON malformato (ConfigFileCorruptedException).
     */
    public function testConstructorThrowsExceptionWhenJsonIsCorrupted(): void
    {
        $this->storageMock->method('fileExists')
            ->with($this->testConfigFile)
            ->willReturn(true);

        $this->storageMock->method('fileRead')
            ->with($this->testConfigFile)
            ->willReturn('{ invalid json content ...');

        $this->expectException(EException::class);

        new EConfigManager($this->testConfigFile, $this->storageMock);
    }

    /**
     * Test eccezione quando la scrittura su file fallisce (ConfigFileNonWritableException).
     */
    public function testSetParamThrowsExceptionWhenFileNotWritable(): void
    {
        $this->setupValidStorageMock();

        // Simula il fallimento della scrittura
        $this->storageMock->method('fileWrite')
            ->willReturn(false);

        $config = new EConfigManager($this->testConfigFile, $this->storageMock);

        $this->expectException(EException::class);

        // Proviamo a salvare un parametro per far scattare la saveCfg() e l'eccezione
        $config->setParam('app_name', 'New App Name');
    }
}
