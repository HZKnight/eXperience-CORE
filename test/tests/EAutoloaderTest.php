<?php

namespace Experience\Tests\Core;

use PHPUnit\Framework\TestCase;
use EAutoloader;
use AutoloaderException;

class EAutoloaderTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();

        // Creiamo una struttura temporanea su disco per simulare le classi da caricare
        $this->tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'exp_autoloader_test_' . uniqid();
        mkdir($this->tempDir, 0777, true);

        // Simuliamo il percorso Experience e vendor
        $_SESSION["experience_path"] = $this->tempDir . DIRECTORY_SEPARATOR;
        
        $expDir = $this->tempDir . DIRECTORY_SEPARATOR . 'Experience';
        $vendorDir = $expDir . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'Psr' . DIRECTORY_SEPARATOR . 'Log';
        
        mkdir($vendorDir, 0777, true);

        // Creiamo un dummy file di classe Vendor
        file_put_contents(
            $vendorDir . DIRECTORY_SEPARATOR . 'LoggerInterface.php',
            "<?php namespace Psr\Log; interface LoggerInterface {}"
        );

        // Creiamo un dummy file di classe Experience (es. Experience\Foo\Bar -> /Experience/foo/bar.class.php)
        $fooDir = $expDir . DIRECTORY_SEPARATOR . 'foo';
        mkdir($fooDir, 0777, true);
        
        file_put_contents(
            $fooDir . DIRECTORY_SEPARATOR . 'bar.class.php',
            "<?php namespace Experience\Foo; class Bar {}"
        );
    }

    protected function tearDown(): void
    {
        // Pulizia ricorsiva della directory temporanea
        $this->removeDirectory($this->tempDir);
        unset($_SESSION["experience_path"]);

        parent::tearDown();
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    // -------------------------------------------------------------------------
    // 1. CONSTRUCTOR & ENVIRONMENT INITIALIZATION TESTS
    // -------------------------------------------------------------------------

    public function testConstructorSetsEnvironmentAndSession(): void
    {
        $autoloader = new EAutoloader();

        $this->assertEquals('v0.2.3-Alfa', getenv('ECORE'));
        $this->assertArrayHasKey('experience_path', $_SESSION);
        $this->assertNotEmpty(getenv('LANG'));
    }

    public function testAutoloaderRegistrationInSPL(): void
    {
        $functionsBefore = spl_autoload_functions();
        
        EAutoloader::register();
        
        $functionsAfter = spl_autoload_functions();
        
        $this->assertGreaterThan(count($functionsBefore), count($functionsAfter));
    }

    // -------------------------------------------------------------------------
    // 2. CLASS LOADING TESTS (Vendor & Experience)
    // -------------------------------------------------------------------------

    public function testLoadsVendorClassSuccessfully(): void
    {
        $autoloader = new EAutoloader();

        // Invocazione diretta per testare il caricamento del file isolato
        $reflection = new \ReflectionClass($autoloader);
        $method = $reflection->getMethod('experienceAutoload');
        $method->setAccessible(true);

        $method->invoke($autoloader, 'Psr\Log\LoggerInterface');

        $this->assertTrue(interface_exists('Psr\Log\LoggerInterface', false));
    }

    public function testLoadsExperienceClassSuccessfully(): void
    {
        $autoloader = new EAutoloader();

        $reflection = new \ReflectionClass($autoloader);
        $method = $reflection->getMethod('experienceAutoload');
        $method->setAccessible(true);

        $method->invoke($autoloader, 'Experience\Foo\Bar');

        $this->assertTrue(class_exists('Experience\Foo\Bar', false));
    }

    public function testIgnoresNonManagedNamespaces(): void
    {
        $autoloader = new EAutoloader();

        $reflection = new \ReflectionClass($autoloader);
        $method = $reflection->getMethod('experienceAutoload');
        $method->setAccessible(true);

        // Se il namespace non è Experience né presente in $vendors, il metodo fa subito return
        $method->invoke($autoloader, 'Some\Other\Framework\Class');

        $this->assertFalse(class_exists('Some\Other\Framework\Class', false));
    }

    // -------------------------------------------------------------------------
    // 3. EXCEPTION HANDLING TESTS
    // -------------------------------------------------------------------------

    public function testThrowsAutoloaderExceptionWhenExperienceClassFileNotFound(): void
    {
        $this->expectException(AutoloaderException::class);
        $this->expectExceptionMessage('Unable to find class: "Experience\NonExistant\DummyClass"');

        $autoloader = new EAutoloader();

        $reflection = new \ReflectionClass($autoloader);
        $method = $reflection->getMethod('experienceAutoload');
        $method->setAccessible(true);

        $method->invoke($autoloader, 'Experience\NonExistant\DummyClass');
    }

    public function testThrowsAutoloaderExceptionWhenVendorFileNotFound(): void
    {
        // 1. Istanziamo l'autoloader
        $autoloader = new EAutoloader();

        // 2. Usiamo una classe della mappa vendor che NON è stata mai caricata negli altri test
        // ad esempio PHPMailer (il cui file non esiste nella nostra directory temporanea)
        $vendorClassToTest = 'PHPMailer\PHPMailer\PHPMailer';

        $this->expectException(AutoloaderException::class);
        $this->expectExceptionMessage('Unable to find class: "' . $vendorClassToTest . '"');

        $reflection = new \ReflectionClass($autoloader);
        $method = $reflection->getMethod('experienceAutoload');
        $method->setAccessible(true);

        // 3. Invoking autoload lancerà l'eccezione poiché il file non esiste in tempDir
        $method->invoke($autoloader, $vendorClassToTest);
    }

    public function testAutoloaderExceptionDefaultErrorCode(): void
    {
        $exception = new AutoloaderException("Test Error");
        $this->assertEquals("AE001", $exception->getCode());
    }
}
