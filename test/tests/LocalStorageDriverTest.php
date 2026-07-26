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

        // Inizializzazione gestore eccezioni del CORE
        EExceptionManager::getExceptionManager();

        // Creazione di un ambiente filesystem isolato per ciascun test
        $this->tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'experience_storage_test_' . uniqid();
        mkdir($this->tempDir, 0777, true);

        $this->driver = new LocalStorageDriver();
    }

    protected function tearDown(): void
    {
        // Pulizia completa della directory temporanea dopo il test
        $this->removeDirectoryRecursive($this->tempDir);
        parent::tearDown();
    }

    /**
     * Helper per la pulizia ricorsiva della cartella temporanea del test.
     */
    private function removeDirectoryRecursive(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            is_dir($path) ? $this->removeDirectoryRecursive($path) : unlink($path);
        }
        rmdir($dir);
    }

    /**
     * Test della connessione allo storage con un percorso esistente.
     */
    public function testConnectToStorageSuccess(): void
    {
        // $path relativo a getcwd()
        $relativePath = str_replace(getcwd(), '', $this->tempDir);

        $this->assertTrue($this->driver->connectToStorage($relativePath));
        $this->assertEquals(getcwd() . $relativePath, $this->driver->getWebRoot());
    }

    /**
     * Test fallimento connessione su percorso inesistente.
     */
    public function testConnectToStorageThrowsExceptionOnInvalidPath(): void
    {
        $this->expectException(EException::class);
        $this->driver->connectToStorage('/non_existing_directory_' . uniqid());
    }

    /**
     * Test creazione directory (mkdir) ed eccezione se già esistente.
     */
    public function testMkdirSuccessAndAlreadyExistsException(): void
    {
        $this->driver->connectToStorage(str_replace(getcwd(), '', $this->tempDir));

        // 1. Creazione riuscita
        $this->assertTrue($this->driver->mkdir('/new_folder', '0777'));
        $this->assertTrue(is_dir($this->tempDir . '/new_folder'));

        // 2. Errore se esiste già
        $this->expectException(EException::class);
        $this->driver->mkdir('/new_folder', '0777');
    }

    /**
     * Test scrittura e lettura file.
     */
    public function testFileWriteAndFileRead(): void
    {
        $this->driver->connectToStorage(str_replace(getcwd(), '', $this->tempDir));

        $fileName = '/sample.txt';
        $content = 'Framework eXperience Core';

        // Write in mode wb
        $this->assertTrue($this->driver->fileWrite($fileName, $content, 'wb'));
        $this->assertTrue($this->driver->fileExists($fileName));

        // Read
        $this->assertEquals($content, $this->driver->fileRead($fileName));
    }

    /**
     * Test lettura di un file inesistente (lancia StorageFileNotFoundException).
     */
    public function testFileReadThrowsExceptionWhenFileNotFound(): void
    {
        $this->driver->connectToStorage(str_replace(getcwd(), '', $this->tempDir));

        $this->expectException(EException::class);
        $this->driver->fileRead('/missing.txt');
    }

    /**
     * Test della comparazione md5 tra due file (fileCompare).
     */
    public function testFileCompareReturnsTrueForIdenticalFiles(): void
    {
        $this->driver->connectToStorage(str_replace(getcwd(), '', $this->tempDir));

        $this->driver->fileWrite('/file1.txt', 'SameContent', 'wb');
        $this->driver->fileWrite('/file2.txt', 'SameContent', 'wb');
        $this->driver->fileWrite('/file3.txt', 'DifferentContent', 'wb');

        $this->assertTrue($this->driver->fileCompare('/file1.txt', '/file2.txt'));
        $this->assertFalse($this->driver->fileCompare('/file1.txt', '/file3.txt'));
    }

    /**
     * Test copia file (fcopy).
     */
    public function testFcopySuccess(): void
    {
        $this->driver->connectToStorage(str_replace(getcwd(), '', $this->tempDir));

        $this->driver->fileWrite('/source.txt', 'Copy payload', 'wb');
        $this->assertTrue($this->driver->fcopy('/source.txt', '/destination.txt'));

        $this->assertTrue($this->driver->fileExists('/destination.txt'));
        $this->assertEquals('Copy payload', $this->driver->fileRead('/destination.txt'));
    }

    /**
     * Test eliminazione ricorsiva file e directory (rm).
     */
    public function testRmDeletesFilesAndNestedDirectories(): void
    {
        $this->driver->connectToStorage(str_replace(getcwd(), '', $this->tempDir));

        // Struttura: /folder/subfolder/file.txt
        $this->driver->mkdir('/folder');
        $this->driver->mkdir('/folder/subfolder');
        $this->driver->fileWrite('/folder/subfolder/file.txt', 'Deep file', 'wb');

        $this->assertTrue($this->driver->isDir('/folder'));

        // Cancellazione ricorsiva
        $this->assertTrue($this->driver->rm('/folder'));
        $this->assertFalse($this->driver->fileExists('/folder'));
    }

    /**
     * Test elenco contenuti directory (ls) con pattern.
     */
    public function testLsListsMatchingFiles(): void
    {
        $this->driver->connectToStorage(str_replace(getcwd(), '', $this->tempDir));

        $this->driver->fileWrite('/alpha.txt', 'A', 'wb');
        $this->driver->fileWrite('/beta.log', 'B', 'wb');
        $this->driver->fileWrite('/gamma.txt', 'G', 'wb');

        $list = $this->driver->ls('/', '*.txt');

        $this->assertCount(2, $list);
        $this->assertContains('alpha.txt', $list);
        $this->assertContains('gamma.txt', $list);
        $this->assertNotContains('beta.log', $list);
    }
}
