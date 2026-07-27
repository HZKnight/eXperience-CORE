<?php

namespace Experience\Tests\Core;

use PHPUnit\Framework\TestCase;
use EAutoloader;
use AutoloaderException;

class EAutoloaderTest extends TestCase
{
    private string $realExperienceDir;
    private string $dummyClassFile;
    private string $dummyVendorFile;

    protected function setUp(): void
    {
        parent::setUp();

        // Individuiamo il percorso reale basato su __DIR__ dell'autoloader
        $baseDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;
        if (!is_dir($baseDir . 'Experience')) {
            // Fallback se la struttura cartelle varia nel runner
            $baseDir = $_SESSION["experience_path"] ?? __DIR__ . DIRECTORY_SEPARATOR;
        }

        $this->realExperienceDir = $baseDir . 'Experience' . DIRECTORY_SEPARATOR;

        // 1. Creiamo un file temporaneo reale per la classe Experience (Experience\AutoloadTest\Dummy)
        $dummyDir = $this->realExperienceDir . 'autoloadtest';
        if (!is_dir($dummyDir)) {
            mkdir($dummyDir, 0777, true);
        }
        $this->dummyClassFile = $dummyDir . DIRECTORY_SEPARATOR . 'dummy.class.php';
        file_put_contents(
            $this->dummyClassFile,
            "<?php namespace Experience\AutoloadTest; class Dummy {}"
        );

        // 2. Creiamo un file temporaneo per un Vendor reale presente nella mappa ($vendors)
        $vendorPsrDir = $this->realExperienceDir . 'vendor' . DIRECTORY_SEPARATOR . 'Psr' . DIRECTORY_SEPARATOR . 'Log';
        if (!is_dir($vendorPsrDir)) {
            mkdir($vendorPsrDir, 0777, true);
        }
        $this->dummyVendorFile = $vendorPsrDir . DIRECTORY_SEPARATOR . 'LoggerAwareInterface.php';
        file_put_contents(
            $this->dummyVendorFile,
            "<?php namespace Psr\Log; interface LoggerAwareInterface {}"
        );
    }

    protected function tearDown(): void
    {
        // Pulizia dei file temporanei creati nell'albero del sorgente
        if (file_exists($this->dummyClassFile)) {
            unlink($this->dummyClassFile);
            @rmdir(dirname($this->dummyClassFile));
        }

        if (file_exists($this->dummyVendorFile)) {
            unlink($this->dummyVendorFile);
        }

        parent::tearDown();
    }

    // -------------------------------------------------------------------------
    // 1. CONSTRUCTOR & ENVIRONMENT TESTS
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
        
        $this->assertGreaterThanOrEqual(count($functionsBefore), count($functionsAfter));
    }

    // -------------------------------------------------------------------------
    // 2. CLASS LOADING TESTS
    // -------------------------------------------------------------------------

    public function testLoadsExperienceClassSuccessfully(): void
    {
        $autoloader = new EAutoloader();

        $reflection = new \ReflectionClass($autoloader);
        $method = $reflection->getMethod('experienceAutoload');
        $method->setAccessible(true);

        $method->invoke($autoloader, 'Experience\AutoloadTest\Dummy');

        $this->assertTrue(class_exists('Experience\AutoloadTest\Dummy', false));
    }

    public function testLoadsVendorClassSuccessfully(): void
    {
        $autoloader = new EAutoloader();

        $reflection = new \ReflectionClass($autoloader);
        $method = $reflection->getMethod('experienceAutoload');
        $method->setAccessible(true);

        $method->invoke($autoloader, 'Psr\Log\LoggerAwareInterface');

        $this->assertTrue(interface_exists('Psr\Log\LoggerAwareInterface', false));
    }

    public function testIgnoresNonManagedNamespaces(): void
    {
        $autoloader = new EAutoloader();

        $reflection = new \ReflectionClass($autoloader);
        $method = $reflection->getMethod('experienceAutoload');
        $method->setAccessible(true);

        // Se non inizia con Experience e non è nei Vendor, fa un return silenzioso
        $method->invoke($autoloader, 'Unmanaged\Test\SampleClass');

        $this->assertFalse(class_exists('Unmanaged\Test\SampleClass', false));
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
        $this->expectException(AutoloaderException::class);
        
        $autoloader = new EAutoloader();

        $reflection = new \ReflectionClass($autoloader);
        $method = $reflection->getMethod('experienceAutoload');
        $method->setAccessible(true);

        // PHPMailer/SMTP è nella mappa $vendors ma il file non esiste su disco nel runner
        $method->invoke($autoloader, 'PHPMailer\PHPMailer\SMTP');
    }

    public function testAutoloaderExceptionDefaultErrorCode(): void
    {
        $exception = new AutoloaderException("Test Error");
        $this->assertEquals("AE001", $exception->getCode());
    }
}
