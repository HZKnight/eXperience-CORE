<?php

namespace Experience\Tests\Core\Tools\Logger;

use PHPUnit\Framework\TestCase;
use Experience\Core\Tools\Logger\ELogger;
use Experience\Core\Tools\Config\EConfigManager;
use Experience\Core\Io\Storage\EStorage;
use Experience\Core\Tools\Logger\ELogLevel;
use Experience\Core\Tools\Logger\ELogRow;
use Psr\Log\LogLevel;
use ReflectionClass;

class ELoggerTest extends TestCase
{
    private $cfgMock;
    private $storageMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Creiamo i mock per le dipendenze obbligatorie del costruttore
        $this->cfgMock = $this->createMock(EConfigManager::class);
        $this->storageMock = $this->createMock(EStorage::class);
    }

    protected function tearDown(): void
    {
        // 1. Svuota il Multiton di ELogger (nota il typo 'instace')
        $loggerReflection = new ReflectionClass(ELogger::class);
        $loggerInstance = $loggerReflection->getProperty('instace');
        $loggerInstance->setAccessible(true);
        $loggerInstance->setValue([]);

        // 2. Svuota lo stato statico di EDbManager se esiste (es. l'istanza singleton)
        // Sostituisci 'instance' o 'instace' con il nome della proprietà reale di EDbManager se ne usa una
        if (class_exists(\Experience\Core\Io\Dbal\EDbManager::class)) {
            $dbReflection = new ReflectionClass(\Experience\Core\Io\Dbal\EDbManager::class);
            if ($dbReflection->hasProperty('instance')) {
                $dbProp = $dbReflection->getProperty('instance');
                $dbProp->setAccessible(true);
                $dbProp->setValue(null);
            }
        }
        
        parent::tearDown();
    }

    /**
     * Test della creazione dell'istanza e del pattern Multiton
     */
    public function testGetLoggerCreatesAndReturnsCorrectInstance(): void
    {
        $loggerName = 'test_channel';
        $logger = ELogger::getLogger($this->cfgMock, $this->storageMock, $loggerName);

        $this->assertInstanceOf(ELogger::class, $logger);
        
        // Verifica che richiamando lo stesso nome venga restituita la stessa istanza
        $sameLogger = ELogger::getLogger($this->cfgMock, $this->storageMock, $loggerName);
        $this->assertSame($logger, $sameLogger);

        // Verifica che l'istanza sia presente nella lista globale
        $instances = ELogger::getIstances();
        $this->assertArrayHasKey($loggerName, $instances);
    }

    /**
     * Test della gestione, aggiunta e rimozione degli appender
     */
    public function testAddAndRemoveAppenders(): void
    {
        $logger = ELogger::getLogger($this->cfgMock, $this->storageMock, 'test_appenders', ELogger::LOG_APPENDER_FILE);
        
        // Verifica l'appender di default impostato nel costruttore
        $list = $logger->get_appenders_list();
        $this->assertCount(1, $list);
        $this->assertEquals([ELogger::LOG_APPENDER_FILE, "FILE"], $list[0]);

        // Aggiunta di un secondo appender (es. DB)
        $logger->add_appender(ELogger::LOG_APPENDER_DB);
        $this->assertCount(2, $logger->get_appenders_list());

        // Rimozione dell'appender FILE
        $logger->remove_appender(ELogger::LOG_APPENDER_FILE);
        $this->assertCount(1, $logger->get_appenders_list());
    }

    /**
     * Test che verifica il lancio dell'eccezione se l'appender non esiste
     */
    public function testGetAppenderThrowsExceptionIfNotFound(): void
    {
        $logger = ELogger::getLogger($this->cfgMock, $this->storageMock, 'test_exception');
        
        // EExceptionManager deve essere configurato o mockato se lancia reali eccezioni nativamente.
        // Assumiamo che lanci una EException (o una classe derivata) tramite EExceptionManager::throwException
        $this->expectException(\Exception::class); 
        
        // Cerchiamo di recuperare un appender mai aggiunto
        $logger->get_appender(ELogger::LOG_APPENDER_EMAIL);
    }

    /**
     * Test dell'interpolazione dei messaggi nel contesto e del passaggio all'appender
     */
    public function testLogInterpolatesContextAndAppendsToAppenders(): void
    {
        $logger = ELogger::getLogger($this->cfgMock, $this->storageMock, 'test_log', ELogger::LOG_APPENDER_FILE);
        
        // Otteniamo l'appender generato internamente per farne il mock parziale o iniettarlo.
        // Poiché gli appender reali vengono istanziati internamente via 'new AppenderFile',
        // un approccio pulito senza refactoring è testare l'effetto di log().
        
        $message = "Utente {username} ha effettuato l'accesso.";
        $context = ['username' => 'Luca'];
        $expectedMessage = "Utente Luca ha effettuato l'accesso.";

        // Usiamo la reflection per intercettare l'Appender interno o semplicemente verifichiamo che il metodo log vada a buon fine.
        // Se vuoi verificare esattamente l'oggetto ELogRow passato all'appender, dovresti esporre un mock sull'appender:
        
        $appenderMock = $this->getMockBuilder(\Experience\Core\Tools\Logger\Appenders\AppenderFile::class)
                             ->disableOriginalConstructor()
                             ->getMock();
                             
        $appenderMock->expects($this->once())
                     ->method('add')
                     ->with($this->callback(function (ELogRow $logrow) use ($expectedMessage) {
                         return $logrow->message === $expectedMessage && $logrow->type === ELogLevel::INFO;
                     }));

        // Sostituiamo l'appender interno con il nostro mock tramite Reflection
        $reflection = new ReflectionClass($logger);
        $appendersProp = $reflection->getProperty('appenders');
        $appendersProp->setAccessible(true);
        $appendersProp->setValue($logger, [ELogger::LOG_APPENDER_FILE => $appenderMock]);

        // Eseguiamo la chiamata tramite il mapping PSR-3 standard
        $logger->log(LogLevel::INFO, $message, $context);
    }

    /**
     * Test dei metodi scorciatoia PSR-3 (info, debug, error, etc.)
     */
    public function testPsr3ShortcutMethods(): void
    {
        $logger = ELogger::getLogger($this->cfgMock, $this->storageMock, 'test_psr3', ELogger::LOG_APPENDER_FILE);
        
        $appenderMock = $this->getMockBuilder(\Experience\Core\Tools\Logger\Appenders\AppenderFile::class)
                             ->disableOriginalConstructor()
                             ->getMock();

        // Ci aspettiamo 2 chiamate: una da alert() e una da debug()
        $appenderMock->expects($this->exactly(2))
                     ->method('add');

        $reflection = new ReflectionClass($logger);
        $appendersProp = $reflection->getProperty('appenders');
        $appendersProp->setAccessible(true);
        $appendersProp->setValue($logger, [ELogger::LOG_APPENDER_FILE => $appenderMock]);

        // Chiamata ai metodi di shortcut
        $logger->alert("System Alert!");
        $logger->debug("Debug info");
    }
}
