<?php

namespace Experience\Tests\Core\Io\Dbal\Driver;

use PHPUnit\Framework\TestCase;
use Experience\Core\Io\Dbal\Driver\SqliteAdapter;
use Experience\Core\Tools\Config\EConfigManager;

class SqliteAdapterTest extends TestCase
{
    private ?SqliteAdapter $adapter = null;

    protected function tearDown(): void
    {
        if ($this->adapter !== null) {
            $this->adapter->disconnect();
            $this->adapter = null;
        }

        parent::tearDown();
    }

    /**
     * Helper per istanziare un adapter connesso ad un DB in memoria (:memory:)
     */
    private function getMemoryAdapter(array $extraConfig = []): SqliteAdapter
    {
        $config = array_merge([
            'path' => ':memory:',
            'tbprefix' => 'exp_'
        ], $extraConfig);

        $adapter = new SqliteAdapter($config);
        $adapter->connect();

        return $adapter;
    }

    /**
     * Helper per creare una tabella temporanea con vari tipi di dati
     */
    private function createDummyTable(SqliteAdapter $adapter): void
    {
        $adapter->execute("CREATE TABLE exp_users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            score REAL,
            is_active INTEGER,
            data BLOB
        )");
    }

    // -------------------------------------------------------------------------
    // 1. CONSTRUCTOR & CONFIGURATION TESTS
    // -------------------------------------------------------------------------

    public function testConstructWithArrayConfig(): void
    {
        $adapter = new SqliteAdapter([
            'path' => ':memory:',
            'tbprefix' => 'test_'
        ]);

        $this->assertInstanceOf(SqliteAdapter::class, $adapter);
        $this->assertEmpty($adapter->getError());
    }

    public function testConstructWithDefaultArrayValues(): void
    {
        $adapter = new SqliteAdapter([]);
        $this->assertInstanceOf(SqliteAdapter::class, $adapter);
    }

    public function testConstructWithEConfigManager(): void
    {
        $configMock = $this->createMock(EConfigManager::class);
        $configMock->method('getParam')
            ->willReturnMap([
                ['db.path', 'database.sqlite', ':memory:'],
                ['db.tbprefix', '', 'exp_']
            ]);

        $adapter = new SqliteAdapter($configMock);
        $this->assertInstanceOf(SqliteAdapter::class, $adapter);
    }

    // -------------------------------------------------------------------------
    // 2. CONNECTION & DISCONNECTION
    // -------------------------------------------------------------------------

    public function testConnectSuccess(): void
    {
        $this->adapter = $this->getMemoryAdapter();
        $this->assertTrue($this->adapter->connect(), 'I richiami successivi a connect() devono restituire true');
    }

    public function testConnectFailureWithInvalidPath(): void
    {
        // Tenta di creare/aprire un file in una directory inesistente
        $adapter = new SqliteAdapter(['path' => '/invalid_dir_xyz_12345/database.sqlite']);
        
        $this->assertFalse($adapter->connect());
        $this->assertStringContainsString('Errore connessione SQLite', $adapter->getError());
    }

    public function testDisconnect(): void
    {
        $this->adapter = $this->getMemoryAdapter();
        $this->adapter->disconnect();

        // Dopo il disconnect, riconnettendosi a :memory: ricrea un DB vuoto ed eseguibile
        $this->assertTrue($this->adapter->connect());
    }

    // -------------------------------------------------------------------------
    // 3. QUERY EXECUTION & BINDINGS (int, float, text, null, blob)
    // -------------------------------------------------------------------------

    public function testExecuteWithMultipleDataTypes(): void
    {
        $this->adapter = $this->getMemoryAdapter();
        $this->createDummyTable($this->adapter);

        // Test di inserimento con tipi differenti per testare bindValue
        $affectedRows = $this->adapter->execute(
            "INSERT INTO exp_users (name, score, is_active, data) VALUES (?, ?, ?, ?)",
            ['Luca Liscio', 98.6, 1, null]
        );

        $this->assertEquals(1, $affectedRows);
        $this->assertGreaterThan(0, $this->adapter->lastInsertId());
    }

    public function testExecuteReturnsFalseOnQueryError(): void
    {
        $this->adapter = $this->getMemoryAdapter();
        
        $result = $this->adapter->execute("INSERT INTO table_non_esiste VALUES (1)");
        
        $this->assertFalse($result);
        $this->assertStringContainsString('Errore esecuzione query', $this->adapter->getError());
    }

    // -------------------------------------------------------------------------
    // 4. FETCH METHODS (fetchAll, fetchOne, fetchColumn)
    // -------------------------------------------------------------------------

    public function testFetchAll(): void
    {
        $this->adapter = $this->getMemoryAdapter();
        $this->createDummyTable($this->adapter);

        $this->adapter->execute("INSERT INTO exp_users (name, score) VALUES (?, ?)", ['User A', 10.5]);
        $this->adapter->execute("INSERT INTO exp_users (name, score) VALUES (?, ?)", ['User B', 20.0]);

        $rows = $this->adapter->fetchAll("SELECT name, score FROM exp_users ORDER BY id ASC");

        $this->assertIsArray($rows);
        $this->assertCount(2, $rows);
        $this->assertEquals('User A', $rows[0]['name']);
        $this->assertEquals(20.0, $rows[1]['score']);
    }

    public function testFetchOne(): void
    {
        $this->adapter = $this->getMemoryAdapter();
        $this->createDummyTable($this->adapter);

        $this->adapter->execute("INSERT INTO exp_users (name, score) VALUES (?, ?)", ['Mario Rossi', 88.0]);

        $row = $this->adapter->fetchOne("SELECT name, score FROM exp_users WHERE name = ?", ['Mario Rossi']);

        $this->assertIsArray($row);
        $this->assertEquals('Mario Rossi', $row['name']);

        // Record non trovato
        $notFound = $this->adapter->fetchOne("SELECT * FROM exp_users WHERE name = ?", ['Inesistente']);
        $this->assertNull($notFound);
    }

    public function testFetchColumn(): void
    {
        $this->adapter = $this->getMemoryAdapter();
        $this->createDummyTable($this->adapter);

        $this->adapter->execute("INSERT INTO exp_users (name, score) VALUES (?, ?)", ['Giuseppe', 42.5]);

        // Offset 0 -> name
        $name = $this->adapter->fetchColumn("SELECT name, score FROM exp_users WHERE name = ?", ['Giuseppe'], 0);
        $this->assertEquals('Giuseppe', $name);

        // Offset 1 -> score
        $score = $this->adapter->fetchColumn("SELECT name, score FROM exp_users WHERE name = ?", ['Giuseppe'], 1);
        $this->assertEquals(42.5, $score);

        // Offset non valido
        $invalid = $this->adapter->fetchColumn("SELECT name FROM exp_users WHERE name = ?", ['Giuseppe'], 99);
        $this->assertNull($invalid);
    }

    // -------------------------------------------------------------------------
    // 5. TRANSACTIONS & SAVEPOINTS
    // -------------------------------------------------------------------------

    public function testTransactionCommit(): void
    {
        $this->adapter = $this->getMemoryAdapter();
        $this->createDummyTable($this->adapter);

        $this->adapter->beginTransaction();
        $this->adapter->execute("INSERT INTO exp_users (name) VALUES (?)", ['TxUser']);
        $this->adapter->commit();

        $count = $this->adapter->fetchColumn("SELECT COUNT(*) FROM exp_users WHERE name = ?", ['TxUser']);
        $this->assertEquals(1, (int)$count);
    }

    public function testTransactionRollback(): void
    {
        $this->adapter = $this->getMemoryAdapter();
        $this->createDummyTable($this->adapter);

        $this->adapter->beginTransaction();
        $this->adapter->execute("INSERT INTO exp_users (name) VALUES (?)", ['RollbackUser']);
        $this->adapter->rollBack();

        $count = $this->adapter->fetchColumn("SELECT COUNT(*) FROM exp_users WHERE name = ?", ['RollbackUser']);
        $this->assertEquals(0, (int)$count);
    }

    public function testNestedTransactionsWithSavepoints(): void
    {
        $this->adapter = $this->getMemoryAdapter();
        $this->createDummyTable($this->adapter);

        // Transazione esterna (Level 0)
        $this->adapter->beginTransaction();
        $this->adapter->execute("INSERT INTO exp_users (name) VALUES (?)", ['OuterUser']);

        // Transazione annidata (Level 1 - SAVEPOINT trans_1)
        $this->adapter->beginTransaction();
        $this->adapter->execute("INSERT INTO exp_users (name) VALUES (?)", ['InnerUser']);
        
        // Rollback al SAVEPOINT trans_1 (cancella solo InnerUser)
        $this->adapter->rollBack();

        // Commit transazione esterna
        $this->adapter->commit();

        $outerCount = $this->adapter->fetchColumn("SELECT COUNT(*) FROM exp_users WHERE name = ?", ['OuterUser']);
        $innerCount = $this->adapter->fetchColumn("SELECT COUNT(*) FROM exp_users WHERE name = ?", ['InnerUser']);

        $this->assertEquals(1, (int)$outerCount, 'Il record esterno deve essere conservato');
        $this->assertEquals(0, (int)$innerCount, 'Il record della transazione annidata deve essere stato annullato');
    }
}
