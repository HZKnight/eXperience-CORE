<?php

namespace Experience\Tests\Core\Exceptions;

use PHPUnit\Framework\TestCase;
use Experience\Core\Exceptions\EException;
use Experience\Core\Exceptions\IException;

class EExceptionTest extends TestCase
{
    // -------------------------------------------------------------------------
    // 1. INSTANTIATION & INTERFACE COMPLIANCE
    // -------------------------------------------------------------------------

    public function testImplementsIExceptionAndExtendsStandardException(): void
    {
        $exception = new EException();

        $this->assertInstanceOf(IException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
    }

    public function testDefaultConstructorValues(): void
    {
        $exception = new EException();

        $this->assertEquals('EException', $exception->getName());
        $this->assertEquals(0, $exception->getCode());
        $this->assertEquals('E000', $exception->getInternalCode());
        $this->assertNotEmpty($exception->getMessage()); // Fallback dgettext('ELang', 'Unknown exception')
    }

    public function testCustomConstructorValues(): void
    {
        $exception = new EException(
            'CustomException',
            'Errore di connessione',
            500,
            'ERR_CONN_01'
        );

        $this->assertEquals('CustomException', $exception->getName());
        $this->assertEquals('Errore di connessione', $exception->getMessage());
        $this->assertEquals(500, $exception->getCode());
        $this->assertEquals('ERR_CONN_01', $exception->getInternalCode());
    }

    public function testConstructorFallbackWhenMessageIsNull(): void
    {
        $exception = new EException('TestException', null, 404, 'E404');

        $this->assertNotEmpty($exception->getMessage());
    }

    // -------------------------------------------------------------------------
    // 2. GETTERS & SETTERS
    // -------------------------------------------------------------------------

    public function testSetAndGetInternalCode(): void
    {
        $exception = new EException();
        $this->assertEquals('E000', $exception->getInternalCode());

        $exception->setInternalCode('SYS_999');
        $this->assertEquals('SYS_999', $exception->getInternalCode());
    }

    public function testSetAndGetName(): void
    {
        $exception = new EException();
        $this->assertEquals('EException', $exception->getName());

        $exception->setName('DatabaseException');
        $this->assertEquals('DatabaseException', $exception->getName());
    }

    // -------------------------------------------------------------------------
    // 3. MESSAGE PREPARATION (strtr)
    // -------------------------------------------------------------------------

    public function testPrepareReplacesVariablesInMessage(): void
    {
        $rawMessage = 'Impossibile connettersi al database {db} sulla porta {port}';
        $exception = new EException('DbException', $rawMessage, 100);

        $exception->prepare([
            '{db}' => 'experience_core',
            '{port}' => '3306'
        ]);

        $expectedMessage = 'Impossibile connettersi al database experience_core sulla porta 3306';
        $this->assertEquals($expectedMessage, $exception->getMessage());
    }

    // -------------------------------------------------------------------------
    // 4. STRING REPRESENTATION (__toString)
    // -------------------------------------------------------------------------

    public function testToStringFormatting(): void
    {
        $exception = new EException('TestEx', 'Messaggio di test', 101);

        $stringOutput = (string)$exception;

        $this->assertStringContainsString('Experience\Core\Exceptions\EException', $stringOutput);
        $this->assertStringContainsString("'Messaggio di test'", $stringOutput);
        $this->assertStringContainsString(__FILE__, $stringOutput);
    }
}
