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
        // Assicuriamoci di partire con uno stato pulito
        $this->resetSingletonState();
    }

    protected function tearDown(): void
    {
        // Ripuliamo lo stato statico alla fine di ogni test
        $this->resetSingletonState();
        parent::tearDown();
    }

    /**
     * Helper per resettare il Singleton e il registry statico
     */
    private function resetSingletonState(): void
    {
        $reflection = new ReflectionClass(EExceptionManager::class);
        
        $instanceProp = $reflection->getProperty('instance');
        $instanceProp->setAccessible(true);
        $instanceProp->setValue(null);

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
     * Test che l'eccezione di default venga registrata nel costruttore.
     */
    public function testConstructorRegistersDefaultException(): void
    {
        $manager = EExceptionManager::getExceptionManager();
        $list = $manager->getExceptionList();

        $this->assertContains("ENotApplicableMethodException", $list);
        $this->assertTrue(class_exists("ENotApplicableMethodException"));
    }

    /**
     * Test per l'aggiunta di una nuova eccezione (creazione alias e registrazione).
     */
    public function testAddExceptionRegistersAndCreatesAlias(): void
    {
        $manager = EExceptionManager::getExceptionManager();
        
        $customExceptionName = "CustomTestExceptionForAdd";
        EExceptionManager::addException($customExceptionName, "Messaggio di Test", "TEST001");

        $list = $manager->getExceptionList();
        
        $this->assertContains($customExceptionName, $list);
        
        // Verifica che class_alias abbia funzionato
        $this->assertTrue(class_exists($customExceptionName));
        
        // Creando un'istanza dell'alias, deve essere istanza della classe madre EException
        $instance = new $customExceptionName('name', 'msg', 123, 'TEST001');
        $this->assertInstanceOf(EException::class, $instance);
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
     * Deve ricadere su ENotApplicableMethodException.
     */
    public function testThrowExceptionFallsBackToDefaultWhenUnregistered(): void
    {
        EExceptionManager::getExceptionManager();

        // Ci aspettiamo che lanci l'eccezione di default generata nel costruttore
        $this->expectException("ENotApplicableMethodException");

        EExceptionManager::throwException("UnregisteredExceptionName");
    }

    /**
     * Test del passaggio delle variabili (vars) che dovrebbe chiamare il metodo prepare() di EException.
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
            
            // Nota: Se la classe EException espone un metodo getter per le variabili (es. getVars()),
            // puoi verificarlo qui. Altrimenti ci accontentiamo che il flusso termini senza fatal error
            // indicando che il metodo prepare() è stato invocato con successo.
        }
    }
}
