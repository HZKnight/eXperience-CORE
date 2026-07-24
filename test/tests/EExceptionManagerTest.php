<?php

namespace Experience\Tests\Core\Exceptions;

use PHPUnit\Framework\TestCase;
use Experience\Core\Exceptions\EExceptionManager;
use Experience\Core\Exceptions\EException;
use ReflectionClass;
use Exception;

class EExceptionManagerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->resetSingletonState();
    }

    protected function tearDown(): void
    {
        $this->resetSingletonState();
        parent::tearDown();
    }

    /**
     * Ripristina lo stato statico del Singleton e del registro,
     * ripristinando anche la registrazione di default.
     */
    private function resetSingletonState(): void
    {
        $reflection = new ReflectionClass(EExceptionManager::class);
        
        // Reset istanza Singleton
        $instanceProp = $reflection->getProperty('instance');
        $instanceProp->setAccessible(true);
        $instanceProp->setValue(null);

        // Reset del registry
        $registryProp = $reflection->getProperty('registry');
        $registryProp->setAccessible(true);
        $registryProp->setValue([]);
    }

    /**
     * Test del pattern Singleton.
     */
    public function testGetExceptionManagerReturnsSingleton(): void
    {
        $instance1 = EExceptionManager::getExceptionManager();
        $instance2 = EExceptionManager::getExceptionManager();

        $this->assertInstanceOf(EExceptionManager::class, $instance1);
        $this->assertSame($instance1, $instance2);
    }

    /**
     * Test che l'eccezione di default venga registrata all'inizializzazione del Singleton.
     */
    public function testConstructorRegistersDefaultException(): void
    {
        // Forziamo la creazione dell'istanza pulita
        $manager = EExceptionManager::getExceptionManager();
        $list = $manager->getExceptionList();

        $this->assertContains("ENotApplicableMethodException", $list);
    }

    /**
     * Test per l'aggiunta di una nuova eccezione (registrazione e alias).
     */
    public function testAddExceptionRegistersAndCreatesAlias(): void
    {
        $manager = EExceptionManager::getExceptionManager();
        
        $customExceptionName = "CustomTestExceptionForAdd";
        EExceptionManager::addException($customExceptionName, "Messaggio di Test", "TEST001");

        $list = $manager->getExceptionList();
        
        $this->assertContains($customExceptionName, $list);
        $this->assertTrue(class_exists($customExceptionName));
    }

    /**
     * Test del lancio di un'eccezione validamente registrata.
     */
    public function testThrowExceptionSuccessfullyThrowsRegisteredException(): void
    {
        EExceptionManager::getExceptionManager();
        
        $exceptionName = "MyValidTestException";
        EExceptionManager::addException($exceptionName, "Valid Message", "VAL001");

        $this->expectException($exceptionName);
        
        EExceptionManager::throwException($exceptionName);
    }

    /**
     * Test del fallback se si prova a lanciare un'eccezione non registrata.
     */
    public function testThrowExceptionFallsBackToDefaultWhenUnregistered(): void
    {
        EExceptionManager::getExceptionManager();

        // Ci assicuriamo che il registro contenga l'eccezione di default
        // anche se la classe era già stata definita in memoria
        EExceptionManager::addException("ENotApplicableMethodException", "Not applicable method exception", "EE000");

        $this->expectException("ENotApplicableMethodException");

        EExceptionManager::throwException("UnregisteredExceptionName");
    }

    /**
     * Test del passaggio delle variabili (vars).
     */
    public function testThrowExceptionPassesVarsToPrepareMethod(): void
    {
        EExceptionManager::getExceptionManager();
        $exceptionName = "ExceptionWithVarsTest";
        
        EExceptionManager::addException($exceptionName, "Message with vars", "VAR001");

        $testVars = ['user_id' => 42, 'action' => 'login'];

        try {
            EExceptionManager::throwException($exceptionName, $testVars);
            $this->fail("L'eccezione non è stata lanciata");
        } catch (Exception $e) {
            $this->assertInstanceOf($exceptionName, $e);
            $this->assertInstanceOf(EException::class, $e);
        }
    }
}
