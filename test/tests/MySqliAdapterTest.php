<?php

namespace Experience\Tests\Core\Io\Dbal\Driver;

use PHPUnit\Framework\TestCase;
use Experience\Core\Io\Dbal\Driver\MysqliAdapter;
use Experience\Core\Tools\Config\EConfigManager;

class MysqliAdapterTest extends TestCase
{
    private ?MysqliAdapter $adapter = null;
    private array $dbConfig;

    protected function setUp(): void
    {
        parent::setUp();

        // Inseriamo tutti i possibili naming per il prefisso come stringhe non-null
        $this->dbConfig = [
            'host'     => getenv('DB_HOST') ?: '127.0.0.1',
            'user'     => getenv('DB_USER') ?: 'root',
            'pass'     => getenv('DB_PASS') !== false ? getenv('DB_PASS') : '',
            'dbname'   => getenv('DB_NAME') ?: 'test_db',
            'port'     => (int)(getenv('DB_PORT') ?: 3306),
            'tbprefix' => 'exp_',
            'dbprefix' => 'exp_',
            'prefix'   => 'exp_'
        ];
    }

    protected function tearDown(): void
    {
        if ($this->adapter !== null) {
            $this->adapter->disconnect();
            $this->adapter = null;
        }

        parent::tearDown();
    }

    /**
     * Helper per verificare la disponibilità del server MySQL/MariaDB
     */
    private function getConnectedAdapter(): ?MysqliAdapter
    {
        $adapter = new MysqliAdapter($this->dbConfig);
        if (!$adapter->connect()) {
            $this->markTestSkipped('Server MySQL non disponibile per i test di integrazione: ' . $adapter->getError());
            return null;
        }

        return $adapter;
    }

    private function createDummyTable(MysqliAdapter $adapter): void
    {
        $adapter->execute("DROP TABLE IF EXISTS exp_test_users");
        $adapter->execute("CREATE TABLE exp_test_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            score DOUBLE NULL,
            is_active TINYINT(1) DEFAULT 1,
            notes TEXT NULL
        ) ENGINE=InnoDB");
    }

    // -------------------------------------------------------------------------
    // 1. CONFIGURATION & CONSTRUCTOR TESTS
    // -------------------------------------------------------------------------

    public function testConstructWithArrayConfig(): void
    {
        $adapter = new MysqliAdapter($this->dbConfig);
        $this->assertInstanceOf(MysqliAdapter::class, $adapter);
        $this->assertEmpty($adapter->getError());
    }

    public function testConstructWithEConfigManager(): void
    {
        $configMock = $this->createMock(EConfigManager::class);
        
        // Ritorna sempre stringhe valide per qualsiasi variant della chiave del prefisso
        $configMock->method('getParam')
            ->willReturnCallback(function (string $key, $default = '') {
                return match ($key) {
                    'db.host', 'host'                   => '127.0.0.1',
                    'db.user', 'user'                   => 'root',
                    'db.pass', 'pass'                   => '',
                    'db.dbname', 'dbname'               => 'test_db',
                    'db.port', 'port'                   => 3306,
                    'db.tbprefix', 'db.dbprefix', 
                    'db.prefix', 'tbprefix', 
                    'dbprefix', 'prefix'                => 'exp_',
                    default                             => is_string($default) ? $default : ''
                };
            });

        $adapter = new MysqliAdapter($configMock);
        $this->assertInstanceOf(MysqliAdapter::class, $adapter);
    }

    // -------------------------------------------------------------------------
    // 2. CONNECTION & ERROR HANDLING TESTS
    // -------------------------------------------------------------------------

    public function testConnectFailureWithInvalidCredentials(): void
    {
        $invalidConfig = array_merge($this->dbConfig, [
            'host' => '127.0.0.1',
            'user' => 'invalid_user_xyz_99',
            'pass' => 'wrong_password_123'
        ]);

        $adapter = new MysqliAdapter($invalidConfig);
        $result = $adapter->connect();

        $this->assertFalse($result);
        $this->assertNotEmpty($adapter->getError());
    }

    public function testMultipleConnectCallsReturnTrueIfAlreadyConnected(): void
    {
        $this->adapter = $this->getConnectedAdapter();
        
        // La seconda chiamata deve subito ritornare true senza riaprire la connessione
        $this->assertTrue($this->adapter->connect());
    }

    public function testDisconnectAndSubsequentQueryFailure(): void
    {
        $this->adapter = $this->getConnectedAdapter();
        $this->adapter->disconnect();

        $result = $this->adapter->execute("SELECT 1");
        $this->assertFalse($result);
    }

    // -------------------------------------------------------------------------
    // 3. QUERY EXECUTION, BINDINGS & TYPES
    // -------------------------------------------------------------------------

    public function testExecuteWithDataTypesBinding(): void
    {
        $this->adapter = $this->getConnectedAdapter();
        $this->createDummyTable($this->adapter);

        // Inserimento con Integer, Double, String e NULL per testare tutti i rami di bind_param (i, d, s, b)
        $affected = $this->adapter->execute(
            "INSERT INTO exp_test_users (name, score, is_active, notes) VALUES (?, ?, ?, ?)",
            ['Luca Liscio', 99.5, 1, null]
        );

        $this->assertEquals(1, $affected);
        $this->assertGreaterThan(0, $this->adapter->lastInsertId());
    }

    public function testExecuteQueryErrorPopulatesErrorMessage(): void
    {
        $this->adapter = $this->getConnectedAdapter();

        $result = $this->adapter->execute("SELECT * FROM non_existent_table_abc_123");

        $this->assertFalse($result);
        $this->assertNotEmpty($this->adapter->getError());
    }

    // -------------------------------------------------------------------------
    // 4. FETCH METHODS
    // -------------------------------------------------------------------------

    public function testFetchAll(): void
    {
        $this->adapter = $this->getConnectedAdapter();
        $this->createDummyTable($this->adapter);

        $this->adapter->execute("INSERT INTO exp_test_users (name, score) VALUES (?, ?)", ['User A', 10.0]);
        $this->adapter->execute("INSERT INTO exp_test_users (name, score) VALUES (?, ?)", ['User B', 20.0]);

        $rows = $this->adapter->fetchAll("SELECT name, score FROM exp_test_users ORDER BY id ASC");

        $this->assertIsArray($rows);
        $this->assertCount(2, $rows);
        $this->assertEquals('User A', $rows[0]['name']);
        $this->assertEquals(20.0, (float)$rows[1]['score']);
    }

    public function testFetchOneReturnsRowOrNull(): void
    {
        $this->adapter = $this->getConnectedAdapter();
        $this->createDummyTable($this->adapter);

        $this->adapter->execute("INSERT INTO exp_test_users (name) VALUES (?)", ['Mario Rossi']);

        $row = $this->adapter->fetchOne("SELECT name FROM exp_test_users WHERE name = ?", ['Mario Rossi']);
        $this->assertIsArray($row);
        $this->assertEquals('Mario Rossi', $row['name']);

        $notFound = $this->adapter->fetchOne("SELECT name FROM exp_test_users WHERE name = ?", ['Inesistente']);
        $this->assertNull($notFound);
    }

    public function testFetchColumnWithOffsets(): void
    {
        $this->adapter = $this->getConnectedAdapter();
        $this->createDummyTable($this->adapter);

        $this->adapter->execute("INSERT INTO exp_test_users (name, score) VALUES (?, ?)", ['Giuseppe', 85.5]);

        // Offset 0 (name)
        $name = $this->adapter->fetchColumn("SELECT name, score FROM exp_test_users WHERE name = ?", ['Giuseppe'], 0);
        $this->assertEquals('Giuseppe', $name);

        // Offset 1 (score)
        $score = $this->adapter->fetchColumn("SELECT name, score FROM exp_test_users WHERE name = ?", ['Giuseppe'], 1);
        $this->assertEquals(85.5, (float)$score);

        // Offset non valido -> deve ritornare null
        $outOfBounds = $this->adapter->fetchColumn("SELECT name FROM exp_test_users WHERE name = ?", ['Giuseppe'], 99);
        $this->assertNull($outOfBounds);
    }

    // -------------------------------------------------------------------------
    // 5. TRANSACTIONS & SAVEPOINTS
    // -------------------------------------------------------------------------

    public function testTransactionCommit(): void
    {
        $this->adapter = $this->getConnectedAdapter();
        $this->createDummyTable($this->adapter);

        $this->adapter->beginTransaction();
        $this->adapter->execute("INSERT INTO exp_test_users (name) VALUES (?)", ['TxUser']);
        $this->adapter->commit();

        $count = $this->adapter->fetchColumn("SELECT COUNT(*) FROM exp_test_users WHERE name = ?", ['TxUser']);
        $this->assertEquals(1, (int)$count);
    }

    public function testTransactionRollback(): void
    {
        $this->adapter = $this->getConnectedAdapter();
        $this->createDummyTable($this->adapter);

        $this->adapter->beginTransaction();
        $this->adapter->execute("INSERT INTO exp_test_users (name) VALUES (?)", ['RollbackUser']);
        $this->adapter->rollBack();

        $count = $this->adapter->fetchColumn("SELECT COUNT(*) FROM exp_test_users WHERE name = ?", ['RollbackUser']);
        $this->assertEquals(0, (int)$count);
    }

    public function testNestedTransactionsWithSavepoints(): void
    {
        $this->adapter = $this->getConnectedAdapter();
        $this->createDummyTable($this->adapter);

        // Transazione Livello 0
        $this->adapter->beginTransaction();
        $this->adapter->execute("INSERT INTO exp_test_users (name) VALUES (?)", ['OuterUser']);

        // Transazione Livello 1 (Savepoint)
        $this->adapter->beginTransaction();
        $this->adapter->execute("INSERT INTO exp_test_users (name) VALUES (?)", ['InnerUser']);
        
        // Rollback al Savepoint (annulla solo InnerUser)
        $this->adapter->rollBack();

        // Commit della transazione principale
        $this->adapter->commit();

        $outerCount = $this->adapter->fetchColumn("SELECT COUNT(*) FROM exp_test_users WHERE name = ?", ['OuterUser']);
        $innerCount = $this->adapter->fetchColumn("SELECT COUNT(*) FROM exp_test_users WHERE name = ?", ['InnerUser']);

        $this->assertEquals(1, (int)$outerCount);
        $this->assertEquals(0, (int)$innerCount);
    }

    // -------------------------------------------------------------------------
    // 6. UTILITIES & ESCAPING
    // -------------------------------------------------------------------------

    public function testEscapeString(): void
    {
        $this->adapter = $this->getConnectedAdapter();

        $escaped = $this->adapter->escape("O'Reilly & \"Co\"");
        $this->assertStringContainsString("O\'Reilly", $escaped);
    }
}
