<?php

namespace Experience\Tests\Core\Io\Net\Mailer;

use PHPUnit\Framework\TestCase;
use Experience\Core\Io\Net\Mailer\EMailer;
use Experience\Core\Io\Net\Mailer\EMessage;
use Experience\Core\Tools\Config\EConfigManager;
use PHPMailer\PHPMailer\PHPMailer;
use ReflectionClass;

class EMailerTest extends TestCase
{
    private $configMock;
    private $messageMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configMock = $this->createMock(EConfigManager::class);
        $this->messageMock = $this->createMock(EMessage::class);
    }

    /**
     * Helper per estrarre l'istanza privata di PHPMailer via Reflection
     */
    private function getInternalMailer(EMailer $emailer): PHPMailer
    {
        $reflection = new ReflectionClass(EMailer::class);
        $property = $reflection->getProperty('mailer');
        $property->setAccessible(true);

        return $property->getValue($emailer);
    }

    /**
     * Test inizializzazione standard senza SMTP.
     */
    public function testConstructorInitializesWithoutSmtpWhenDisabled(): void
    {
        $this->configMock->method('getParam')
            ->willReturnCallback(function (string $param) {
                if ($param === 'mail.is_smtp') {
                    return false;
                }
                return null;
            });

        $emailer = new EMailer($this->configMock);
        $phpMailer = $this->getInternalMailer($emailer);

        $this->assertInstanceOf(PHPMailer::class, $phpMailer);
        $this->assertEquals('mail', $phpMailer->Mailer); // Driver di default di PHPMailer
    }

    /**
     * Test configurazione SMTP attiva nel costruttore.
     */
    public function testConstructorEnablesSmtpWhenConfigured(): void
    {
        $configMap = [
            'mail.is_smtp'       => true,
            'mail.smtp_host'     => 'smtp.example.com',
            'mail.smtp_port'     => 587,
            'mail.smtp_auth'     => true,
            'mail.smtp_username' => 'user@example.com',
            'mail.smtp_passwd'   => 'secret',
            'mail.smtp_secure'   => 'tls',
        ];

        $this->configMock->method('getParam')
            ->willReturnCallback(fn($param) => $configMap[$param] ?? null);

        $emailer = new EMailer($this->configMock);
        $phpMailer = $this->getInternalMailer($emailer);

        $this->assertEquals('smtp', $phpMailer->Mailer);
        $this->assertEquals('smtp.example.com', $phpMailer->Host);
        $this->assertEquals(587, $phpMailer->Port);
        $this->assertTrue($phpMailer->SMTPAuth);
        $this->assertEquals('user@example.com', $phpMailer->Username);
        $this->assertEquals('secret', $phpMailer->Password);
        $this->assertEquals('tls', $phpMailer->SMTPSecure);
    }

    /**
     * Test dell'aggiunta allegati con e senza il parametro nome custom.
     */
    public function testAddAttachmentDelegatesToPhpMailer(): void
    {
        $this->configMock->method('getParam')->willReturn(false);
        $emailer = new EMailer($this->configMock);

        // Creiamo un file temporaneo reale per evitare che PHPMailer scatti eccezione file-not-found
        $tmpFile = tempnam(sys_get_temp_dir(), 'exp_test_');
        file_put_contents($tmpFile, 'content');

        $resultNoName = $emailer->addAttachment('', $tmpFile);
        $resultWithName = $emailer->addAttachment('custom_name.txt', $tmpFile);

        $this->assertTrue($resultNoName);
        $this->assertTrue($resultWithName);

        // Cleanup del file temporaneo
        @unlink($tmpFile);
    }

    /**
     * Test di invio email con successo (ritorna stringa vuota "").
     */
    public function testSendReturnsEmptyStringOnSuccess(): void
    {
        $configMap = [
            'mail.is_smtp'      => false,
            'mail.sender_email' => 'sender@example.com',
            'mail.sender_name'  => 'eXperience System',
        ];

        $this->configMock->method('getParam')
            ->willReturnCallback(fn($param) => $configMap[$param] ?? null);

        // Mock dei dati del messaggio
        $this->messageMock->isHTML = true;
        $this->messageMock->method('getAddress')->willReturn(['recipient@example.com']);
        $this->messageMock->method('getCC')->willReturn(['cc@example.com']);
        $this->messageMock->method('getBCC')->willReturn([]);
        $this->messageMock->method('getAttachments')->willReturn([]);
        $this->messageMock->method('getSubject')->willReturn('Test Subject');
        $this->messageMock->method('getBody')->willReturn('<p>Test Body</p>');

        $emailer = new EMailer($this->configMock);

        // Sostituiamo il mailer con un mock per catturare il metodo send() senza inviare davvero
        $phpMailerMock = $this->createMock(PHPMailer::class);
        $phpMailerMock->expects($this->once())
            ->method('send')
            ->willReturn(true);

        $reflection = new ReflectionClass(EMailer::class);
        $property = $reflection->getProperty('mailer');
        $property->setAccessible(true);
        $property->setValue($emailer, $phpMailerMock);

        $result = $emailer->send($this->messageMock);

        $this->assertEquals('', $result);
    }

    /**
     * Test gestione dell'errore di invio (ritorna messaggio d'errore del Mailer).
     */
    public function testSendReturnsErrorMessageOnFailure(): void
    {
        $configMap = [
            'mail.is_smtp'      => false,
            'mail.sender_email' => 'invalid-email',
            'mail.sender_name'  => 'eXperience System',
        ];

        $this->configMock->method('getParam')
            ->willReturnCallback(fn($param) => $configMap[$param] ?? null);

        $this->messageMock->isHTML = false;
        $this->messageMock->method('getAddress')->willReturn(['recipient@example.com']);
        $this->messageMock->method('getCC')->willReturn([]);
        $this->messageMock->method('getBCC')->willReturn([]);
        $this->messageMock->method('getAttachments')->willReturn([]);
        $this->messageMock->method('getSubject')->willReturn('Fail Test');
        $this->messageMock->method('getBody')->willReturn('Plain body');

        $emailer = new EMailer($this->configMock);

        // Simuliamo un'eccezione lanciata da PHPMailer durante l'invio
        $phpMailerMock = $this->createMock(PHPMailer::class);
        $phpMailerMock->ErrorInfo = 'SMTP connection failed';
        $phpMailerMock->expects($this->once())
            ->method('send')
            ->willThrowException(new \PHPMailer\PHPMailer\Exception('Mail error'));

        $reflection = new ReflectionClass(EMailer::class);
        $property = $reflection->getProperty('mailer');
        $property->setAccessible(true);
        $property->setValue($emailer, $phpMailerMock);

        $result = $emailer->send($this->messageMock);

        $this->assertStringContainsString('Message could not be sent. Mailer Error: SMTP connection failed', $result);
    }
}
