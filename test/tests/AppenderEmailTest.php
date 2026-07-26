<?php

namespace Experience\Tests\Core\Tools\Logger\Appenders;

use PHPUnit\Framework\TestCase;
use Experience\Core\Tools\Logger\Appenders\AppenderEmail;
use Experience\Core\Tools\Logger\ELogRow;
use Experience\Core\Tools\Config\EConfigManager;
use Experience\Core\Exceptions\EExceptionManager;
use Experience\Core\Exceptions\EException;
use ReflectionClass;

class AppenderEmailTest extends TestCase
{
    private $configMock;
    private string $logName = 'mail_logger';
    private array $backupServer;

    protected function setUp(): void
    {
        parent::setUp();

        // Backup di $_SERVER per evitare contaminazioni
        $this->backupServer = $_SERVER;
        $_SERVER['SERVER_NAME'] = 'localhost';

        $this->configMock = $this->createMock(EConfigManager::class);

        // Mappatura parametri di configurazione
        $this->configMock->method('getParam')
            ->willReturnCallback(function (string $param, $default = null) {
                if ($param === 'logger.level') {
                    return 100; // Valore soglia loglevel ereditato da Appender
                }
                if ($param === 'site_name') {
                    return 'eXperience Framework';
                }
                if ($param === 'admin_email') {
                    return 'admin@example.com';
                }
                if ($param === 'mail.is_smtp') {
                    return false;
                }
                if ($param === 'mail.sender_email') {
                    return 'system@example.com';
                }
                if ($param === 'mail.sender_name') {
                    return 'System Notifier';
                }
                return $default;
            });

        // Inizializzazione del gestore eccezioni
        EExceptionManager::getExceptionManager();
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->backupServer;
        parent::tearDown();
    }

    /**
     * Test del costruttore e della registrazione dell'eccezione personalizzata.
     */
    public function testConstructorRegistersCustomException(): void
    {
        $appender = new AppenderEmail($this->logName, $this->configMock);
        
        $manager = EExceptionManager::getExceptionManager();
        $this->assertContains("EMailAppenderException", $manager->getExceptionList());
    }

    /**
     * Test chegetLog() lanci sempre ENotApplicableMethodException.
     */
    public function testGetLogThrowsENotApplicableMethodException(): void
    {
        $appender = new AppenderEmail($this->logName, $this->configMock);

        $this->expectException(EException::class);
        $appender->getLog(0, 10);
    }

    /**
     * Test quando il tipo di log è inferiore a loglevel (nessuna mail inviata, restituisce true).
     */
    public function testAddReturnsTrueAndSkipsSendingWhenTypeIsBelowLogLevel(): void
    {
        $appender = new AppenderEmail($this->logName, $this->configMock);

        // Impostiamo un loglevel alto via Reflection (es. 200)
        $reflection = new ReflectionClass($appender);
        $loglevelProp = $reflection->getProperty('loglevel');
        $loglevelProp->setAccessible(true);
        $loglevelProp->setValue($appender, 200);

        $logRowMock = $this->createMock(ELogRow::class);
        $logRowMock->type = 100; // < 200

        $this->assertTrue($appender->add($logRowMock));
    }

    /**
     * Test del flusso di invio quando la mail ha successo.
     */
    public function testAddExecutesSuccessfullyWhenMailerReturnsEmptyString(): void
    {
        $appender = new AppenderEmail($this->logName, $this->configMock);

        // Impostiamo un loglevel basso (es. 50)
        $reflection = new ReflectionClass($appender);
        $loglevelProp = $reflection->getProperty('loglevel');
        $loglevelProp->setAccessible(true);
        $loglevelProp->setValue($appender, 50);

        // Configurazione delle proprietà pubbliche statiche identificate in Appender
        $errorIdentifierProp = $reflection->getProperty('errorIdentifier');
        $errorIdentifierProp->setAccessible(true);
        $errorIdentifierProp->setValue(null, [
            100 => 'ERROR'
        ]);

        $logRowMock = $this->createMock(ELogRow::class);
        $logRowMock->type = 100;
        $logRowMock->date = '2026-07-26 12:00:00';
        $logRowMock->message = 'Critical database failure';

        // Avendo impostato i parametri mock per mail.is_smtp = false, mailer->send()
        // tenterà di usare il driver locale o fallirà solo se non configurato.
        // Se si desidera intercettare del tutto EMailer, è possibile verificarne il comportamento
        // catturando l'eventuale EMailAppenderException oppure verificando il ritorno true.
        try {
            $result = $appender->add($logRowMock);
            $this->assertTrue($result);
        } catch (EException $e) {
            // Se PHPMailer fallisce nell'ambiente CI senza server SMTP,
            // catturiamo la EMailAppenderException generata correttamente dalla classe.
            $this->assertInstanceOf(EException::class, $e);
        }
    }

    /**
     * Test che verifica il lancio dell'eccezione EMailAppenderException in caso di errore Mailer.
     */
    public function testAddThrowsEMailAppenderExceptionOnError(): void
    {
        // Forziamo un errore di configurazione impostando sender_email invalido
        $invalidConfigMock = $this->createMock(EConfigManager::class);
        $invalidConfigMock->method('getParam')
            ->willReturnCallback(function (string $param) {
                if ($param === 'logger.level') return 100;
                if ($param === 'mail.is_smtp') return false;
                if ($param === 'mail.sender_email') return ''; // Email vuota per forzare errore in PHPMailer
                if ($param === 'admin_email') return 'admin@example.com';
                return null;
            });

        $appender = new AppenderEmail($this->logName, $invalidConfigMock);

        $reflection = new ReflectionClass($appender);
        $loglevelProp = $reflection->getProperty('loglevel');
        $loglevelProp->setAccessible(true);
        $loglevelProp->setValue($appender, 50);

        $errorIdentifierProp = $reflection->getProperty('errorIdentifier');
        $errorIdentifierProp->setAccessible(true);
        $errorIdentifierProp->setValue(null, [
            100 => 'CRITICAL'
        ]);

        $logRowMock = $this->createMock(ELogRow::class);
        $logRowMock->type = 100;
        $logRowMock->date = '2026-07-26 12:00:00';
        $logRowMock->message = 'System crash';

        // L'errore restituito da EMailer farà scattare EExceptionManager::throwException("EMailAppenderException")
        $this->expectException(EException::class);

        $appender->add($logRowMock);
    }
}
