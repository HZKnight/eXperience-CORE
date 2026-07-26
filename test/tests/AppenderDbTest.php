<?php

namespace Experience\Tests\Core\Tools\Logger\Appenders;

use PHPUnit\Framework\TestCase;
use Experience\Core\Tools\Logger\Appenders\AppenderDb;
use Experience\Core\Tools\Logger\ELogRow;
use Experience\Core\Tools\Config\EConfigManager;
use Experience\Core\Io\Dbal\EDbManager;
use ReflectionClass;

class AppenderDbTest extends TestCase
{
    private $configMock;
    private $dbMock;
    private string $logName = 'app_system';

    protected function setUp(): void
    {
        parent::setUp();

        $this->configMock = $this->createMock(EConfigManager::class);
        $this->dbMock = $this->createMock(EDbManager::class);

        // Mappatura parametri base per la configurazione del DB
        $this->configMock->method('getParam')
            ->willReturnCallback(function (string $param, $default = null) {
                if ($param === 'db.tb_prefix') {
                    return 'exp_';
                }
                if ($param === 'logger.level') {
                    return 100; // Valore soglia di loglevel per l'Appender padre
                }
                return $default;
            });
    }

    /**
     * Helper per iniettare il Mock di EDbManager all'interno di AppenderDb via Reflection.
     */
    private function createAppenderDbWithMockedDb(): AppenderDb
    {
        $appender = new AppenderDb($this->logName, $this->configMock);

        $reflection = new ReflectionClass(AppenderDb::class);
        $property = $reflection->getProperty('db');
        $property->setAccessible(true);
        $property->setValue($appender, $this->dbMock);

        return $appender;
    }

    /**
     * Test del metodo getLog per il recupero dei dati paginati.
     */
    public function testGetLogFetchesSubsetFromDatabase(): void
    {
        $expectedRows = [
            ['id' => 1, 'logger_id' => 1, 'message' => 'First Log', 'created_at' => '2026-07-26 10:00:00']
        ];

        $this->dbMock->expects($this->once())
            ->method('getRowSubSet')
            ->with('exp_logger', 0, 10, 'created_at', 'DESC')
            ->willReturn($expectedRows);

        $appender = $this->createAppenderDbWithMockedDb();
        $result = $appender->getLog(0, 10);

        $this->assertEquals($expectedRows, $result);
    }

    /**
     * Test quando la riga di log ha un tipo inferiore a loglevel (deve ignorare l'inserimento e ritornare true).
     */
    public function testAddReturnsTrueWhenLogLevelIsBelowThreshold(): void
    {
        $appender = $this->createAppenderDbWithMockedDb();

        // Impostiamo il loglevel ereditato dal padre ad un valore alto (es. 200)
        $reflection = new ReflectionClass($appender);
        $loglevelProp = $reflection->getProperty('loglevel');
        $loglevelProp->setAccessible(true);
        $loglevelProp->setValue($appender, 200);

        // Il logger esiste già nel DB
        $this->dbMock->method('doQuery')->willReturn([['id' => 1, 'name' => $this->logName]]);
        $this->dbMock->method('getError')->willReturn('');

        // Non deve eseguire l'INSERT del log_row
        $this->dbMock->expects($this->never())->method('doUpdate');

        $logRowMock = $this->createMock(ELogRow::class);
        $logRowMock->type = 100; // < 200

        $this->assertTrue($appender->add($logRowMock));
    }

    /**
     * Test inserimento log di successo (logger esistente).
     */
    public function testAddInsertsLogRowSuccessfullyWhenLoggerExists(): void
    {
        $appender = $this->createAppenderDbWithMockedDb();

        // Impostiamo loglevel a 50
        $reflection = new ReflectionClass($appender);
        $loglevelProp = $reflection->getProperty('loglevel');
        $loglevelProp->setAccessible(true);
        $loglevelProp->setValue($appender, 50);

        // Simulazione query createLogger e getLoggerId (logger già presente)
        $this->dbMock->method('doQuery')
            ->with("SELECT * FROM `exp_logger` WHERE `name` = ?", [1 => $this->logName])
            ->willReturn([['id' => 5, 'name' => $this->logName]]);

        $this->dbMock->method('getError')->willReturn('');
        $this->dbMock->method('covertToSqlDate')->willReturn('2026-07-26 12:00:00');

        $this->dbMock->expects($this->once())
            ->method('doUpdate')
            ->with(
                "INSERT INTO `exp_logger_rows` (`logger_id`, `level`, `message`, `context`, `created_at`) VALUES (?, ?, ?, ?, ?)",
                [
                    1 => 5,
                    2 => 100,
                    3 => 'System initialized',
                    4 => '',
                    5 => '2026-07-26 12:00:00'
                ]
            )
            ->willReturn(['affected_rows' => 1]);

        $logRowMock = $this->createMock(ELogRow::class);
        $logRowMock->type = 100;
        $logRowMock->message = 'System initialized';
        $logRowMock->date = '2026-07-26 12:00:00';

        $this->assertTrue($appender->add($logRowMock));
    }

    /**
     * Test inserimento log di successo con auto-creazione del logger se non esiste ancora.
     */
    public function testAddCreatesLoggerIfNotExistsAndInsertsLogRow(): void
    {
        $appender = $this->createAppenderDbWithMockedDb();

        $reflection = new ReflectionClass($appender);
        $loglevelProp = $reflection->getProperty('loglevel');
        $loglevelProp->setAccessible(true);
        $loglevelProp->setValue($appender, 50);

        // La prima doQuery (createLogger) e seconda (getLoggerId) ritornano prima vuoto, poi l'ID creato
        $this->dbMock->expects($this->exactly(2))
            ->method('doQuery')
            ->willReturnOnConsecutiveCalls(
                [], // createLogger -> logger non ancora presente
                [['id' => 12, 'name' => $this->logName]] // getLoggerId -> ritornato dopo la creazione
            );

        $this->dbMock->method('getError')->willReturn('');
        $this->dbMock->method('covertToSqlDate')->willReturn('2026-07-26 12:00:00');

        // Prima chiamata doUpdate per createLogger, seconda per insertLogRow
        $this->dbMock->expects($this->exactly(2))
            ->method('doUpdate')
            ->willReturnOnConsecutiveCalls(
                ['affected_rows' => 1],
                ['affected_rows' => 1]
            );

        $logRowMock = $this->createMock(ELogRow::class);
        $logRowMock->type = 100;
        $logRowMock->message = 'First system run';
        $logRowMock->date = '2026-07-26 12:00:00';

        $this->assertTrue($appender->add($logRowMock));
    }

    /**
     * Test fallimento di add() se si verifica un errore durante createLogger().
     */
    public function testAddReturnsFalseOnCreateLoggerDatabaseError(): void
    {
        $appender = $this->createAppenderDbWithMockedDb();

        // doQuery restituisce null anziché false per rispecchiare la firma ?array di EDbManager
        $this->dbMock->method('doQuery')->willReturn(null);
        $this->dbMock->method('getError')->willReturn('Database connection lost');

        $logRowMock = $this->createMock(ELogRow::class);

        $this->assertFalse($appender->add($logRowMock));
    }

    /**
     * Test fallimento di add() se doUpdate restituisce un array con la chiave 'error'.
     */
    public function testAddReturnsFalseWhenInsertLogRowFails(): void
    {
        $appender = $this->createAppenderDbWithMockedDb();

        $reflection = new ReflectionClass($appender);
        $loglevelProp = $reflection->getProperty('loglevel');
        $loglevelProp->setAccessible(true);
        $loglevelProp->setValue($appender, 50);

        $this->dbMock->method('doQuery')->willReturn([['id' => 1, 'name' => $this->logName]]);
        $this->dbMock->method('getError')->willReturn('');

        // Simula il fallimento con la chiave 'error' nell'array ritornato da doUpdate
        $this->dbMock->method('doUpdate')->willReturn(['error' => 'Disk full']);

        $logRowMock = $this->createMock(ELogRow::class);
        $logRowMock->type = 100;
        $logRowMock->message = 'Test message';
        $logRowMock->date = '2026-07-26 12:00:00'; // Impostato per evitare TypeError in covertToSqlDate

        $this->assertFalse($appender->add($logRowMock));
    }
}
