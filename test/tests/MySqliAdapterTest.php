<?php

namespace Experience\Tests\Core\Io\Dbal\Driver;

use PHPUnit\Framework\TestCase;
use Experience\Core\Io\Dbal\Driver\MySqliAdapter;
use Experience\Core\Tools\Config\EConfigManager;

class MySqliAdapterTest extends TestCase
{
    private array $dbConfig;
    private ?MySqliAdapter $adapter = null;

    protected function setUp(): void
    {
        parent::setUp();

        // Configurazione di default per i test (modificabile via variabili d'ambiente)
        $this->dbConfig = [
            'host'      => getenv('DB_HOST') ?: '127.0.0.1',
            'port'      => (int)(getenv('DB_PORT') ?: 3306),
            'uname'     => getenv('DB_USER') ?: 'root',
            'passwd'    => getenv('DB_PASS') ?: '',
            'db'        => getenv('DB_NAME') ?: 'test_db',
            'tb_prefix' => 'exp_'
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
     * Helper interno per verificare la presenza di una connessione attiva prima di eseguire query reali.
     */
    private function getConnectedAdapter(): ?MySqliAdapter
    {
        $adapter = new MySqliAdapter($this->dbConfig);
        if (!$adapter->connect()) {
            $this->markTestSkipped('Database MySQL non raggiungibile. Test skippato: ' . $adapter->getError());
        }

        return $adapter;
    }

    /**
     * Helper per la creazione rapida di una tabella temporanea di test.
     */
    private function createDummyTable(MySqliAdapter $adapter): void
    {
        $adapter->execute("CREATE TEMPORARY TABLE exp_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL,
            score DOUBLE NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
    }

    // -------------------------------------------------------------------------
    // 1. CONSTRUCTOR & INITIALIZATION TESTS
    // -------------------------------------------------------------------------

    public function testConstructWithArrayConfig(): void
    {
        $adapter = new MySqliAdapter($this->dbConfig);
        $this->assertInstanceOf(MySqliAdapter::class, $adapter);
        $this->assertEmpty($adapter->getError());
    }

    public function testConstructWithEConfigManager(): void
    {
        $configMock = $this->createMock(EConfigManager::class);
        $configMock->method('getParam')
            ->willReturnMap([
                ['db.host', 'localhost', $this->dbConfig['host']],
                ['db.port', 3306, $this->dbConfig['port']],
                ['db.uname', '', $this->dbConfig['uname']],
                ['db.passwd', '', $this->dbConfig['passwd']],
                ['db.tb_prefix', '', $this->dbConfig['tb_prefix']],
                ['db.db', '', $this->dbConfig['db']],
            ]);

        $adapter = new MySqliAdapter($configMock);
        $this->assertInstanceOf(MySqliAdapter::class, $adapter);
    }

    // -------------------------------------------------------------------------
    // 2. CONNECTION & ERROR HANDLING TESTS
    // -------------------------------------------------------------------------

    public function testConnectSuccess(): void
    {
        $adapter = $this->getConnectedAdapter();
        $this->assertTrue($adapter->connect(), 'I richiami successivi a connect() devono restituire true');
    }

    public function testConnectFailureWithInvalidCredentials(): void
    {
        $invalidConfig = $this->dbConfig;
        $invalidConfig['passwd'] = 'invalid_password_xyz_123456';
        $invalidConfig['port'] = 3306;

        $adapter = new MySqliAdapter($invalidConfig);
        
        // Disattiviamo temporaneamente il reporting strict globale se si vuole catturare il bool false
        $this->assertFalse($adapter->connect());
        $this->assertNotEmpty($adapter->getError());
    }

    public function testDisconnect(): void
    {
        $adapter = $this->getConnectedAdapter();
        $adapter->disconnect();

        // Verifichiamo che dopo la disconnessione sia possibile riconnettersi puliti
        $this->assertTrue($adapter->connect());
    }

    // -------------------------------------------------------------------------
    // 3. CRUD & QUERY EXECUTION TESTS
    // -------------------------------------------------------------------------

    public function testExecuteInsertAndUpdate(): void
    {
        $this->adapter = $this->getConnectedAdapter();
        $this->createDummyTable($this->adapter);

        // INSERT con binding di tipi diversi (string, double, int)
        $affectedRows = $this->adapter->execute(
            "INSERT INTO exp_users (name, score) VALUES (?, ?)",
            ['Luca Liscio', 95.5]
        );

        $this->assertEquals(1, $affectedRows);
        $this->assertGreaterThan(0, $this->adapter->lastInsertId());

        // UPDATE
        $updatedRows = $this->adapter->execute(
            "UPDATE exp_users SET score = ? WHERE name = ?",
            [100.0, 'Luca Liscio']
        );

        $this->assertEquals(1, $updatedRows);
    }

    public function testExecuteReturnsFalseOnQueryError(): void
    {
        $this->adapter = $this->getConnectedAdapter();
        
        $result = $this->adapter->execute("SELECT * FROM table_that_does_not_exist_xyz");
        
        $this->assertFalse($result);
        $this->assertNotEmpty($this->adapter->getError());
    }

    // -------------------------------------------------------------------------
    // 4. FETCH METHODS TESTS (fetchAll, fetchOne, fetchColumn)
    // -------------------------------------------------------------------------

    public function testFetchAll(): void
    {
        $this->adapter = $this->getConnectedAdapter();
        $this->createDummyTable($this->adapter);

        $this->adapter->execute("INSERT INTO exp_users (name, score) VALUES (?, ?)", ['User A', 10.0]);
        $this->adapter->execute("INSERT INTO exp_users (name, score) VALUES (?, ?)", ['User B', 20.0]);

        $rows = $this->adapter->fetchAll("SELECT name, score FROM exp_users ORDER BY id ASC");

        $this->assertIsArray($rows);
        $this->assertCount(2, $rows);
        $this->assertEquals('User A', $rows[0]['name']);
        $this->assertEquals('User B', $rows[1]['name']);
    }

    public function testFetchOne(): void
    {
        $this->adapter = $this->getConnectedAdapter();
        $this->createDummyTable($this->adapter);

        $this->adapter->execute("INSERT INTO exp_users (name, score) VALUES (?, ?)", ['Mario Rossi', 88.0]);

        $row = $this->adapter->fetchOne("SELECT name, score FROM exp_users WHERE name = ?", ['Mario Rossi']);

        $this->assertIsArray($row);
        $this->assertEquals('Mario Rossi', $row['name']);
        $this->assertEquals(88.0, (float)$row['score']);

        // Test riscontro record inesistente
        $notFound = $this->adapter->fetchOne("SELECT * FROM exp_users WHERE name = ?", ['NonEsisto']);
        $this->assertNull($notFound);
    }

    public function testFetchColumn(): void
    {
        $this->adapter = $this->getConnectedAdapter();
        $this->createDummyTable($this->adapter);

        $this->adapter->execute("INSERT INTO exp_users (name, score) VALUES (?, ?)", ['Giuseppe', 42.0]);

        // Offset 0 (prima colonna -> name)
        $valCol0 = $this->adapter->fetchColumn("SELECT name, score FROM exp_users WHERE name = ?", ['Giuseppe'], 0);
        $this->assertEquals('Giuseppe', $valCol0);

        // Offset 1 (seconda colonna -> score)
        $valCol1 = $this->adapter->fetchColumn("SELECT name, score FROM exp_users WHERE name = ?", ['Giuseppe'], 1);
        $this->assertEquals(42.0, (float)$valCol1);

        // Offset fuori limite
        $valInvalid = $this->adapter->fetchColumn("SELECT name FROM exp_users WHERE name = ?", ['Giuseppe'], 99);
        $this->assertNull($valInvalid);
    }

    // -------------------------------------------------------------------------
    // 5. TRANSACTIONS & SAVEPOINTS TESTS
    // -------------------------------------------------------------------------

    public function testTransactionCommit(): void
    {
        $this->adapter = $this->getConnectedAdapter();
        $this->createDummyTable($this->adapter);

        $this->adapter->beginTransaction();
        $this->adapter->execute("INSERT INTO exp_users (name, score) VALUES (?, ?)", ['TxUser1', 50.0]);
        $this->adapter->commit();

        $count = $this->adapter->fetchColumn("SELECT COUNT(*) FROM exp_users WHERE name = ?", ['TxUser1']);
        $this->assertEquals(1, (int)$count);
    }

    public function testTransactionRollback(): void
    {
        $this->adapter = $this->getConnectedAdapter();
        $this->createDummyTable($this->adapter);

        $this->adapter->beginTransaction();
        $this->adapter->execute("INSERT INTO exp_users (name, score) VALUES (?, ?)", ['RollbackUser', 10.0]);
        $this->adapter->rollBack();

        $count = $this->adapter->fetchColumn("SELECT COUNT(*) FROM exp_users WHERE name = ?", ['RollbackUser']);
        $this->assertEquals(0, (int)$count);
    }

    public function testNestedTransactionsWithSavepoints(): void
    {
        $this->adapter = $this->getConnectedAdapter();
        $this->createDummyTable($this->adapter);

        // Transazione Livello 0
        $this->adapter->beginTransaction();
        $this->adapter->execute("INSERT INTO exp_users (name, score) VALUES (?, ?)", ['OuterUser', 1.0]);

        // Transazione Annidata Livello 1 (Crea SAVEPOINT trans_1)
        $this->adapter->beginTransaction();
        $this->adapter->execute("INSERT INTO exp_users (name, score) VALUES (?, ?)", ['InnerUser', 2.0]);
        
        // Rollback al Savepoint (annulla solo InnerUser)
        $this->adapter->rollBack();

        // Commit della transazione principale
        $this->adapter->commit();

        $outerCount = $this->adapter->fetchColumn("SELECT COUNT(*) FROM exp_users WHERE name = ?", ['OuterUser']);
        $innerCount = $this->adapter->fetchColumn("SELECT COUNT(*) FROM exp_users WHERE name = ?", ['InnerUser']);

        $this->assertEquals(1, (int)$outerCount, 'Il record esterno deve essere conservato');
        $this->assertEquals(0, (int)$innerCount, 'Il record annidato deve essere stato annullato');
    }
}
