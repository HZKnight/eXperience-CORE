<?php

namespace Experience\Tests\Core\Tools\Logger\Appenders;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Experience\Core\Tools\Logger\Appenders\AppenderFile;
use Experience\Core\Tools\Config\EConfigManager;
use Experience\Core\Io\Storage\EStorage;
use Experience\Core\Tools\Logger\ELogRow;
use Experience\Core\Exceptions\EException;

class AppenderFileTest extends TestCase
{
    private MockObject&EConfigManager $cfgMock;
    private MockObject&EStorage $storageMock;
    private string $logName = 'app';
    private string $expectedFileName;

    protected function setUp(): void
    {
        parent::setUp();

        // Prepariamo la sessione di fallback per experience_path
        if (!isset($_SESSION)) {
            $_SESSION = [];
        }
        $_SESSION["experience_path"] = DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR;

        /** @var EConfigManager&MockObject */
        $this->cfgMock = $this->createMock(EConfigManager::class);

        /** @var EStorage&MockObject */
        $this->storageMock = $this->createMock(EStorage::class);

        // Nome file calcolato dinamicamente come nell'appender (app_DDMMYYYY.log)
        $this->expectedFileName = $this->logName . '_' . date('dmY') . '.log';
    }

    protected function tearDown(): void
    {
        unset($_SESSION["experience_path"]);
        parent::tearDown();
    }

    /**
     * Helper per creare un'istanza DTO di ELogRow
     */
    private function createLogRow(int $type, string $message = 'Test message', string $date = '2026-07-28 10:00:00'): ELogRow
    {
        $logRow = new ELogRow();
        $logRow->type = $type;
        $logRow->message = $message;
        $logRow->date = $date;

        return $logRow;
    }

    // -------------------------------------------------------------------------
    // 1. INIZIALIZZAZIONE & CONFIGURAZIONE PERCORSI
    // -------------------------------------------------------------------------

    public function testConstructorUsesConfiguredLogPath(): void
    {
        $customPath = DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'log';

        $this->cfgMock->expects($this->once())
            ->method('has')
            ->with('log_path')
            ->willReturn(true);

        $this->cfgMock->expects($this->once())
            ->method('getParam')
            ->with('log_path')
            ->willReturn($customPath);

        // Ci assicuriamo che il mock risponda isDir = true per superare createLogDir
        $this->storageMock->method('isDir')->willReturn(true);

        $appender = new AppenderFile($this->logName, $this->cfgMock, $this->storageMock);

        // Proviamo a scrivere una riga per verificare che utilizzi la cartella configurata
        $expectedFilePath = $customPath . DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR . $this->expectedFileName;

        $this->storageMock->expects($this->once())
            ->method('fileExists')
            ->with($expectedFilePath)
            ->willReturn(false);

        $this->storageMock->expects($this->once())
            ->method('fileCreate')
            ->with($expectedFilePath, $this->anything());

        $appender->add($this->createLogRow(1));
    }

    public function testConstructorFallbackToSessionExperiencePath(): void
    {
        $this->cfgMock->method('has')->with('log_path')->willReturn(false);
        $this->storageMock->method('isDir')->willReturn(true);

        $appender = new AppenderFile($this->logName, $this->cfgMock, $this->storageMock);

        $expectedFilePath = $_SESSION["experience_path"] . 'log' . DIRECTORY_SEPARATOR . $this->expectedFileName;

        $this->storageMock->expects($this->once())
            ->method('fileCreate')
            ->with($expectedFilePath, $this->anything());

        $appender->add($this->createLogRow(1));
    }

    public function testSetLogDirUpdatesBaseDirectoryAndFilePath(): void
    {
        $this->cfgMock->method('has')->willReturn(false);
        $this->storageMock->method('isDir')->willReturn(true);

        $appender = new AppenderFile($this->logName, $this->cfgMock, $this->storageMock);

        $newDir = DIRECTORY_SEPARATOR . 'custom' . DIRECTORY_SEPARATOR . 'log_dir';
        $appender->setLogDir($newDir);

        $expectedNewFilePath = $newDir . DIRECTORY_SEPARATOR . $this->expectedFileName;

        $this->storageMock->expects($this->once())
            ->method('fileCreate')
            ->with($expectedNewFilePath, $this->anything());

        $appender->add($this->createLogRow(1));
    }

    // -------------------------------------------------------------------------
    // 2. SCRITTURA LOG (add)
    // -------------------------------------------------------------------------

    public function testAddCreatesNewFileIfFileDoesNotExist(): void
    {
        $this->cfgMock->method('has')->willReturn(false);
        $this->storageMock->method('isDir')->willReturn(true);
        $this->storageMock->method('fileExists')->willReturn(false);

        $appender = new AppenderFile($this->logName, $this->cfgMock, $this->storageMock);
        
        // Impostiamo il livello minimo di log dell'Appender
        $appender->setLogLevel(1);

        $logRow = $this->createLogRow(1, 'Database connection error', '2026-07-28 10:15:00');

        $this->storageMock->expects($this->once())
            ->method('fileCreate')
            ->with(
                $this->anything(),
                $this->callback(function (string $content) {
                    return str_contains($content, '(2026-07-28 10:15:00)')
                        && str_contains($content, 'Database connection error')
                        && str_ends_with($content, "\n");
                })
            );

        $result = $appender->add($logRow);
        $this->assertTrue($result);
    }

    public function testAddAppendsToFileIfFileAlreadyExists(): void
    {
        $this->cfgMock->method('has')->willReturn(false);
        $this->storageMock->method('isDir')->willReturn(true);
        $this->storageMock->method('fileExists')->willReturn(true);

        $appender = new AppenderFile($this->logName, $this->cfgMock, $this->storageMock);
        $appender->setLogLevel(1);

        $logRow = $this->createLogRow(2, 'Warning message');

        $this->storageMock->expects($this->once())
            ->method('fileWrite')
            ->with($this->anything(), $this->anything(), 'a');

        $result = $appender->add($logRow);
        $this->assertTrue($result);
    }

    public function testAddSkipsWritingIfLogLevelIsLowerThanConfigured(): void
    {
        $this->cfgMock->method('has')->willReturn(false);
        $this->storageMock->method('isDir')->willReturn(true);

        $appender = new AppenderFile($this->logName, $this->cfgMock, $this->storageMock);
        
        // Impostiamo il livello di log a 3 (es. ERROR)
        $appender->setLogLevel(3);

        // Inviamo un log di livello 1 (es. INFO) -> non deve scrivere
        $logRow = $this->createLogRow(1, 'Debug message');

        // Non deve tentare né di creare né di scrivere sul file
        $this->storageMock->expects($this->never())->method('fileCreate');
        $this->storageMock->expects($this->never())->method('fileWrite');

        $result = $appender->add($logRow);
        $this->assertTrue($result);
    }

    // -------------------------------------------------------------------------
    // 3. LETTURA LOG (getLog)
    // -------------------------------------------------------------------------

    public function testGetLogReturnsSliceOfLines(): void
    {
        $this->cfgMock->method('has')->willReturn(false);
        
        $mockedLines = [
            "Line 0\n",
            "Line 1\n",
            "Line 2\n",
            "Line 3\n",
            "Line 4\n"
        ];

        $this->storageMock->method('fileExists')->willReturn(true);
        $this->storageMock->method('fileRead')->willReturn($mockedLines);

        $appender = new AppenderFile($this->logName, $this->cfgMock, $this->storageMock);

        // Chiediamo da riga 1 a riga 3 (deve restituire 2 elementi: indice 1 e 2)
        $result = $appender->getLog(1, 3);

        $this->assertCount(2, $result);
        $this->assertEquals(["Line 1\n", "Line 2\n"], array_values($result));
    }

    public function testGetLogReturnsAllLinesFromStartWhenStopIsNull(): void
    {
        $this->cfgMock->method('has')->willReturn(false);
        
        $mockedLines = ["Line 0\n", "Line 1\n", "Line 2\n"];

        $this->storageMock->method('fileExists')->willReturn(true);
        $this->storageMock->method('fileRead')->willReturn($mockedLines);

        $appender = new AppenderFile($this->logName, $this->cfgMock, $this->storageMock);

        // Invochiamo con stop null (passando 0 viene convertito a null dall'if di normalizzazione o chiamato come tale)
        $result = $appender->getLog(1, 0);

        $this->assertCount(2, $result);
        $this->assertEquals(["Line 1\n", "Line 2\n"], array_values($result));
    }

    public function testGetLogThrowsExceptionWhenLogFileDoesNotExist(): void
    {
        $this->cfgMock->method('has')->willReturn(false);
        $this->storageMock->method('fileExists')->willReturn(false);

        $appender = new AppenderFile($this->logName, $this->cfgMock, $this->storageMock);

        $this->expectException(EException::class);

        $appender->getLog(0, 10);
    }

    // -------------------------------------------------------------------------
    // 4. CREAZIONE CARTELLA LOG & ECCEZIONI (createLogDir)
    // -------------------------------------------------------------------------

    public function testAddCreatesDirectoryIfItDoesNotExist(): void
    {
        $this->cfgMock->method('has')->willReturn(false);
        
        // La cartella non esiste inizialmente, ma mkdir ha successo
        $this->storageMock->expects($this->exactly(2))
            ->method('isDir')
            ->willReturnOnConsecutiveCalls(false, true);

        $this->storageMock->expects($this->once())
            ->method('mkdir')
            ->with($this->anything(), 0777)
            ->willReturn(true);

        $appender = new AppenderFile($this->logName, $this->cfgMock, $this->storageMock);
        $appender->add($this->createLogRow(1));
    }

    public function testAddThrowsExceptionIfDirectoryCreationFails(): void
    {
        $this->cfgMock->method('has')->willReturn(false);

        // La cartella non esiste e mkdir fallisce
        $this->storageMock->method('isDir')->willReturn(false);
        $this->storageMock->method('mkdir')->willReturn(false);

        $appender = new AppenderFile($this->logName, $this->cfgMock, $this->storageMock);

        $this->expectException(EException::class);

        $appender->add($this->createLogRow(1));
    }
}
