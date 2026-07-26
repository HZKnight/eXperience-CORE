<?php

namespace Experience\Tests\Core\Io\Storage;

use PHPUnit\Framework\TestCase;
use Experience\Core\Io\Storage\EStorage;
use Experience\Core\Io\Storage\Driver\StorageDriver;
use Experience\Core\Exceptions\EExceptionManager;
use Experience\Core\Exceptions\EException;
use ReflectionClass;

class EStorageTest extends TestCase
{
    private $driverMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Ripristiniamo la mappa delle istanze statiche prima di ogni test per isolare l'ambiente
        $this->resetEStorageInstances();

        // Mock base del driver
        $this->driverMock = $this->createMock(StorageDriver::class);
        $this->driverMock->method('connectToStorage')->willReturn(true);
        $this->driverMock->method('getWebRoot')->willReturn('/var/www/uploads/');

        // Inizializziamo il gestore delle eccezioni per EExceptionManager
        EExceptionManager::getExceptionManager();
    }

    protected function tearDown(): void
    {
        $this->resetEStorageInstances();
        parent::tearDown();
    }

    /**
     * Helper via Reflection per resettare l'array statico $instace di EStorage tra i test.
     */
    private function resetEStorageInstances(): void
    {
        $reflection = new ReflectionClass(EStorage::class);
        $property = $reflection->getProperty('instace');
        $property->setAccessible(true);
        $property->setValue(null, []);
    }

    /**
     * Test della creazione e del recupero dell'istanza Singleton/Multiton.
     */
    public function testGetStorageReturnsSameInstanceForSameName(): void
    {
        $storage1 = EStorage::getStorage('local', $this->driverMock);
        $storage2 = EStorage::getStorage('local', $this->driverMock);

        $this->assertInstanceOf(EStorage::class, $storage1);
        $this->assertSame($storage1, $storage2);
    }

    /**
     * Test del recupero di tutte le istanze registrate tramite getInstances().
     */
    public function testGetInstancesReturnsAllCreatedStorages(): void
    {
        $storage1 = EStorage::getStorage('local', $this->driverMock);
        $storage2 = EStorage::getStorage('s3', $this->driverMock);

        $instances = EStorage::getInstances();

        $this->assertCount(2, $instances);
        $this->assertArrayHasKey('local', $instances);
        $this->assertArrayHasKey('s3', $instances);
        $this->assertSame($storage1, $instances['local']);
        $this->assertSame($storage2, $instances['s3']);
    }

    /**
     * Test di delega diretta dei metodi pass-through al driver.
     */
    public function testProxyMethodsDelegateToStorageDriver(): void
    {
        $this->driverMock->expects($this->once())
            ->method('mkdir')
            ->with('docs', '0755')
            ->willReturn(true);

        $this->driverMock->expects($this->once())
            ->method('rm')
            ->with('temp.txt')
            ->willReturn(true);

        $this->driverMock->expects($this->once())
            ->method('fcopy')
            ->with('src.txt', 'dst.txt')
            ->willReturn(true);

        $this->driverMock->expects($this->once())
            ->method('ls')
            ->with('./', '*.txt')
            ->willReturn(['file1.txt', 'file2.txt']);

        $this->driverMock->expects($this->once())
            ->method('fileCompare')
            ->with('a.txt', 'b.txt')
            ->willReturn(true);

        $this->driverMock->expects($this->once())
            ->method('fileWrite')
            ->with('test.txt', 'hello', 'a')
            ->willReturn(true);

        $this->driverMock->expects($this->once())
            ->method('fileRead')
            ->with('test.txt')
            ->willReturn('hello');

        $this->driverMock->expects($this->once())
            ->method('isDir')
            ->with('docs')
            ->willReturn(true);

        $storage = EStorage::getStorage('test', $this->driverMock);

        $this->assertTrue($storage->mkdir('docs', '0755'));
        $this->assertTrue($storage->rm('temp.txt'));
        $this->assertTrue($storage->fcopy('src.txt', 'dst.txt'));
        $this->assertEquals(['file1.txt', 'file2.txt'], $storage->ls('./', '*.txt'));
        $this->assertTrue($storage->fileCompare('a.txt', 'b.txt'));
        $this->assertTrue($storage->fileWrite('test.txt', 'hello', 'a'));
        $this->assertEquals('hello', $storage->fileRead('test.txt'));
        $this->assertTrue($storage->isDir('docs'));
    }

    /**
     * Test creazione file di successo tramite fileCreate().
     */
    public function testFileCreateSuccess(): void
    {
        // Prima del fileWrite il file non deve esistere, poi dopo la scrittura deve esistere
        $this->driverMock->expects($this->exactly(2))
            ->method('fileExists')
            ->with('newfile.txt')
            ->willReturnOnConsecutiveCalls(false, true);

        $this->driverMock->expects($this->once())
            ->method('fileWrite')
            ->with('newfile.txt', 'Sample content', 'wb')
            ->willReturn(true);

        $storage = EStorage::getStorage('test', $this->driverMock);

        $this->assertTrue($storage->fileCreate('newfile.txt', 'Sample content'));
    }

    /**
     * Test fallimento fileCreate() se il file esiste già (deve lanciare StorageFileAlreadyExistException).
     */
    public function testFileCreateThrowsExceptionIfFileAlreadyExists(): void
    {
        $this->driverMock->method('fileExists')
            ->with('existing.txt')
            ->willReturn(true);

        $storage = EStorage::getStorage('test', $this->driverMock);

        $this->expectException(EException::class);
        $storage->fileCreate('existing.txt', 'data');
    }

    /**
     * Test fallimento fileCreate() se la scrittura fallisce (deve lanciare StorageFileNotWritableException).
     */
    public function testFileCreateThrowsExceptionIfWriteFails(): void
    {
        $this->driverMock->method('fileExists')
            ->with('readonly.txt')
            ->willReturn(false);

        $this->driverMock->method('fileWrite')
            ->with('readonly.txt', 'data', 'wb')
            ->willReturn(false);

        $storage = EStorage::getStorage('test', $this->driverMock);

        $this->expectException(EException::class);
        $storage->fileCreate('readonly.txt', 'data');
    }
}
