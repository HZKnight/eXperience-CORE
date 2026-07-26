<?php

namespace Experience\Tests\Core\Io\Storage\Driver;

use PHPUnit\Framework\TestCase;
use Experience\Core\Io\Storage\Driver\LocalStorageDriver;
use Experience\Core\Exceptions\EExceptionManager;
use Experience\Core\Exceptions\EException;

class LocalStorageDriverTest extends TestCase
{
    private LocalStorageDriver $driver;
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();

        // Inizializzazione delle eccezioni fittizie per il test
        EExceptionManager::getExceptionManager();
        EExceptionManager::addException("StorageConnectionException", "Impossibile connettersi.", "ST001");
        EExceptionManager::addException("StorageDirectoryNotCreatedException", "Directory [DIR] con mode [MODE] non creata.", "ST002");
        EExceptionManager::addException("StorageDirectoryAlreadyExistException", "Directory [DIR] già esistente.", "ST003");
        EExceptionManager::addException("StorageFileNotFoundException", "File [FILE] non trovato.", "ST004");
        EExceptionManager::addException("StorageFileNotWritableException", "File [FILE] non scrivibile.", "ST005");
        EExceptionManager::addException("StorageFileListingException", "Errore ls [SOURCE] [PATTERN].", "ST006");

        // Creazione cartella temporanea di test
        $this->tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'exp_test_' . uniqid() . DIRECTORY_SEPARATOR;
        mkdir($this->tempDir, 0777, true);

        $this->driver = new LocalStorageDriver();
    }

    protected function tearDown(): void
    {
        $this->removeDirectoryRecursive($this->tempDir);
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

    /**
     * Connette il driver assicurando che il percorso termini SEMPRE con '/'
     */
    private function connectDriverToTempDir(): void
    {
        // Normalizziamo i separatori sostituendo i backslash con slash
        $cwd = str_replace('\\', '/', getcwd());
        $realTemp = str_replace('\\', '/', realpath($this->tempDir) ?: $this->tempDir);

        if (strpos($realTemp, $cwd) === 0) {
            $relativePath = substr($realTemp, strlen($cwd));
        } else {
            $relativePath = $realTemp;
        }

        $relativePath = rtrim($relativePath, '/') . '/';
        $this->driver->connectToStorage($relativePath);
    }

    public function testConnectToStorageSuccess(): void
    {
        $cwd = getcwd();
        $realTemp = realpath($this->tempDir) ?: $this->tempDir;
        $relativePath = stristr($realTemp, $cwd) ? substr($realTemp, strlen($cwd)) : $realTemp;
        $relativePath = rtrim($relativePath, '/\\') . '/';

        $this->assertTrue($this->driver->connectToStorage($relativePath));
        $this->assertEquals($cwd . $relativePath, $this->driver->getWebRoot());
    }

    public function testConnectToStorageThrowsExceptionOnInvalidPath(): void
    {
        $this->expectException(EException::class);
        $this->driver->connectToStorage('/non_existing_dir_' . uniqid() . '/');
    }

    public function testMkdirSuccessAndAlreadyExistsException(): void
    {
        $this->connectDriverToTempDir();

        // Creazione cartella (senza slash iniziale per concatenare pulito)
        $this->assertTrue($this->driver->mkdir('new_folder', '0777'));
        $this->assertTrue($this->driver->isDir('new_folder'));

        // Eccezione se già esistente
        $this->expectException(EException::class);
        $this->driver->mkdir('new_folder', '0777');
    }

    public function testFileWriteAndFileRead(): void
    {
        $this->connectDriverToTempDir();

        $fileName = 'sample.txt';
        $content = 'Framework eXperience Core';

        $this->assertTrue($this->driver->fileWrite($fileName, $content, 'wb'));
        $this->assertTrue($this->driver->fileExists($fileName));
        $this->assertEquals($content, $this->driver->fileRead($fileName));
    }

    public function testFileReadThrowsExceptionWhenFileNotFound(): void
    {
        $this->connectDriverToTempDir();

        $this->expectException(EException::class);
        $this->driver->fileRead('missing.txt');
    }

    public function testFileCompareReturnsTrueForIdenticalFiles(): void
    {
        $this->connectDriverToTempDir();

        $this->driver->fileWrite('file1.txt', 'SameContent', 'wb');
        $this->driver->fileWrite('file2.txt', 'SameContent', 'wb');
        $this->driver->fileWrite('file3.txt', 'DifferentContent', 'wb');

        $this->assertTrue($this->driver->fileCompare('file1.txt', 'file2.txt'));
        $this->assertFalse($this->driver->fileCompare('file1.txt', 'file3.txt'));
    }

    public function testFcopySuccess(): void
    {
        $this->connectDriverToTempDir();

        $this->driver->fileWrite('source.txt', 'Copy payload', 'wb');
        $this->assertTrue($this->driver->fcopy('source.txt', 'destination.txt'));

        $this->assertTrue($this->driver->fileExists('destination.txt'));
        $this->assertEquals('Copy payload', $this->driver->fileRead('destination.txt'));
    }

    public function testRmDeletesFilesAndNestedDirectories(): void
    {
        $this->connectDriverToTempDir();

        $this->driver->mkdir('folder', '0777');
        $this->driver->mkdir('folder/subfolder', '0777');
        $this->driver->fileWrite('folder/subfolder/file.txt', 'Deep file', 'wb');

        $this->assertTrue($this->driver->isDir('folder'));

        // Cancellazione ricorsiva
        $this->assertTrue($this->driver->rm('folder'));
        $this->assertFalse($this->driver->fileExists('folder'));
    }

    public function testLsListsMatchingFiles(): void
    {
        $this->connectDriverToTempDir();

        $this->driver->fileWrite('alpha.txt', 'A', 'wb');
        $this->driver->fileWrite('beta.log', 'B', 'wb');
        $this->driver->fileWrite('gamma.txt', 'G', 'wb');

        // Passando '' (stringa vuota) scansiona direttamente webRoot senza aggiungere './'
        $list = $this->driver->ls('', '*.txt');

        $this->assertCount(2, $list);
        $this->assertContains('alpha.txt', $list);
        $this->assertContains('gamma.txt', $list);
        $this->assertNotContains('beta.log', $list);
    }
}
