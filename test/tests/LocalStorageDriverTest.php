<?php

namespace Experience\Tests\Core\Io\Storage\Driver;

use PHPUnit\Framework\TestCase;
use Experience\Core\Io\Storage\Driver\LocalStorageDriver;
use Experience\Core\Exceptions\EExceptionManager;
use Experience\Core\Exceptions\EException;

class LocalStorageDriverTest extends TestCase
{
    private LocalStorageDriver $driver;
    private string $relativeTempDir;
    private string $absoluteTempDir;

    protected function setUp(): void
    {
        parent::setUp();

        // Inizializzazione eccezioni
        EExceptionManager::getExceptionManager();
        EExceptionManager::addException("StorageConnectionException", "Impossibile connettersi.", "ST001");
        EExceptionManager::addException("StorageDirectoryNotCreatedException", "Directory [DIR] con mode [MODE] non creata.", "ST002");
        EExceptionManager::addException("StorageDirectoryAlreadyExistException", "Directory [DIR] già esistente.", "ST003");
        EExceptionManager::addException("StorageFileNotFoundException", "File [FILE] non trovato.", "ST004");
        EExceptionManager::addException("StorageFileNotWritableException", "File [FILE] non scrivibile.", "ST005");
        EExceptionManager::addException("StorageFileListingException", "Errore ls [SOURCE] [PATTERN].", "ST006");

        // Creiamo la cartella temporanea DENTRO la radice del progetto per garantire un percorso relativo valido
        $uniqueId = uniqid('storage_test_');
        $this->relativeTempDir = 'tmp/' . $uniqueId . '/';
        $this->absoluteTempDir = getcwd() . '/' . $this->relativeTempDir;

        if (!is_dir($this->absoluteTempDir)) {
            mkdir($this->absoluteTempDir, 0777, true);
        }

        $this->driver = new LocalStorageDriver();
    }

    protected function tearDown(): void
    {
        $this->removeDirectoryRecursive($this->absoluteTempDir);
        parent::tearDown();
    }

    private function removeDirectoryRecursive(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            is_dir($path) ? $this->removeDirectoryRecursive($path) : @unlink($path);
        }
        @rmdir($dir);
    }

    private function connectDriver(): void
    {
        $this->driver->connectToStorage($this->relativeTempDir);
    }

    public function testConnectToStorageSuccess(): void
    {
        $this->assertTrue($this->driver->connectToStorage($this->relativeTempDir));
    }

    public function testConnectToStorageThrowsExceptionOnInvalidPath(): void
    {
        $this->expectException(EException::class);
        $this->driver->connectToStorage('tmp/non_existing_directory_' . uniqid() . '/');
    }

    public function testMkdirSuccessAndAlreadyExistsException(): void
    {
        $this->connectDriver();

        $this->assertTrue($this->driver->mkdir('new_folder', '0777'));
        $this->assertTrue($this->driver->isDir('new_folder'));

        $this->expectException(EException::class);
        $this->driver->mkdir('new_folder', '0777');
    }

    public function testFileWriteAndFileRead(): void
    {
        $this->connectDriver();

        $fileName = 'sample.txt';
        $content = 'Framework eXperience Core';

        $this->assertTrue($this->driver->fileWrite($fileName, $content, 'wb'));
        $this->assertTrue($this->driver->fileExists($fileName));
        $this->assertEquals($content, $this->driver->fileRead($fileName));
    }

    public function testFileReadThrowsExceptionWhenFileNotFound(): void
    {
        $this->connectDriver();

        $this->expectException(EException::class);
        $this->driver->fileRead('missing.txt');
    }

    public function testFileCompareReturnsTrueForIdenticalFiles(): void
    {
        $this->connectDriver();

        $this->driver->fileWrite('file1.txt', 'SameContent', 'wb');
        $this->driver->fileWrite('file2.txt', 'SameContent', 'wb');
        $this->driver->fileWrite('file3.txt', 'DifferentContent', 'wb');

        $this->assertTrue($this->driver->fileCompare('file1.txt', 'file2.txt'));
        $this->assertFalse($this->driver->fileCompare('file1.txt', 'file3.txt'));
    }

    public function testFcopySuccess(): void
    {
        $this->connectDriver();

        $this->driver->fileWrite('source.txt', 'Copy payload', 'wb');
        $this->assertTrue($this->driver->fcopy('source.txt', 'destination.txt'));

        $this->assertTrue($this->driver->fileExists('destination.txt'));
        $this->assertEquals('Copy payload', $this->driver->fileRead('destination.txt'));
    }

    public function testRmDeletesFilesAndNestedDirectories(): void
    {
        $this->connectDriver();

        $this->driver->mkdir('folder', '0777');
        $this->driver->mkdir('folder/subfolder', '0777');
        $this->driver->fileWrite('folder/subfolder/file.txt', 'Deep file', 'wb');

        $this->assertTrue($this->driver->isDir('folder'));

        $this->assertTrue($this->driver->rm('folder'));
        $this->assertFalse($this->driver->fileExists('folder'));
    }

    public function testLsListsMatchingFiles(): void
    {
        $this->connectDriver();

        $this->driver->fileWrite('alpha.txt', 'A', 'wb');
        $this->driver->fileWrite('beta.log', 'B', 'wb');
        $this->driver->fileWrite('gamma.txt', 'G', 'wb');

        // Passiamo '' per scansionare la root configurata in connectToStorage
        $list = $this->driver->ls('', '*.txt');

        $this->assertCount(2, $list);
        $this->assertContains('alpha.txt', $list);
        $this->assertContains('gamma.txt', $list);
        $this->assertNotContains('beta.log', $list);
    }
}
