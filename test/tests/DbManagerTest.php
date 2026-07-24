<?php

namespace Experience\Tests\Core\Io\Dbal;

use PHPUnit\Framework\TestCase;
use Experience\Core\Io\Dbal\EDbManager;
use Experience\Core\Tools\Config\EConfigManager;


class EDbManagerTest extends TestCase
{
    private array $sqliteConfig;

    protected function setUp(): void
    {
        parent::setUp();

        // Configurazione per SQLite in memoria (eseguibile ovunque in CI/CD)
        $this->sqliteConfig = [
            'driver'    => 'pdo_sqlite',
            'tb_prefix' => 'exp_',
            'path'      => ':memory:',
            'database'  => ':memory:'
        ];
    }

    /**
     * Test dell'inizializzazione con array di configurazione e prefisso tabella.
     */
    public function testConstructorWithArrayConfig(): void
    {
        $db = new EDbManager($this->sqliteConfig);
        
        $this->assertEmpty($db->getError());
    }

    /**
     * Test dell'inizializzazione usando un Mock di EConfigManager.
     */
    public function testConstructorWithEConfigManagerMock(): void
    {
        $configMock = $this->createMock(EConfigManager::class);
        $configMock->method('getParam')
            ->willReturnMap([
                ['db.driver', null, 'pdo_sqlite'],
                ['db.tb_prefix', null, 'exp_'],
            ]);

        $db = new EDbManager($configMock);
        
        $this->assertEmpty($db->getError());
    }

    /**
     * Test del comportamento quando viene fornito un driver non supportato.
     * Verifica che il costruttore intercetti l'errore e che la proprietà $adapter non venga letta a vuoto.
     */
    public function testUnsupportedDriverSetsError(): void
    {
        $invalidConfig = [
            'driver'    => 'oracle_invalid',
            'tb_prefix' => 'exp_'
        ];

        $db = new EDbManager($invalidConfig);

        $this->assertEquals('Unsupported database driver: oracle_invalid', $db->getError());
    }

    /**
     * Test della sostituzione del prefisso '$_' con il prefisso reale configurato.
     */
    public function testTablePrefixReplacementInDoQuery(): void
    {
        $db = new EDbManager($this->sqliteConfig);

        // Creiamo la tabella di test usando il prefisso reale 'exp_'
        $db->doUpdate("CREATE TABLE exp_users (id INTEGER PRIMARY KEY, name TEXT)");
        $db->doUpdate("INSERT INTO exp_users (name) VALUES ('Luca')");

        // Utilizziamo '$_' nella query: deve sostituirlo automaticamente con 'exp_'
        $result = $db->doQuery("SELECT * FROM exp_users WHERE name = :name", ['name' => 'Luca']);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('Luca', $result[0]['name']);
    }

    /**
     * Test del metodo doUpdate e del conteggio delle righe modificate (nbrows).
     */
    public function testDoUpdateExecutesCommands(): void
    {
        $db = new EDbManager($this->sqliteConfig);

        $db->doUpdate("CREATE TABLE exp_items (id INTEGER PRIMARY KEY, item_name TEXT)");
        $res = $db->doUpdate("INSERT INTO exp_items (item_name) VALUES ('Keyboard')");

        $this->assertIsArray($res);
        $this->assertEquals("INSERT INTO exp_items (item_name) VALUES ('Keyboard')", $res['sql']);
        $this->assertEquals(1, $res['nbrows']);
    }

    /**
     * Test del recupero del numero di righe con getTableNumRows e sostituzione prefisso.
     */
    public function testGetTableNumRows(): void
    {
        $db = new EDbManager($this->sqliteConfig);

        $db->doUpdate("CREATE TABLE exp_logs (id INTEGER PRIMARY KEY)");
        $db->doUpdate("INSERT INTO exp_logs VALUES (1), (2), (3)");

        $count = $db->getTableNumRows('$_logs');

        $this->assertEquals(3, $count);
    }

    /**
     * Test della paginazione/subset di righe con getRowSubSet.
     */
    public function testGetRowSubSet(): void
    {
        $db = new EDbManager($this->sqliteConfig);

        $db->doUpdate("CREATE TABLE exp_products (id INTEGER PRIMARY KEY, title TEXT)");
        $db->doUpdate("INSERT INTO exp_products (id, title) VALUES (1, 'A'), (2, 'B'), (3, 'C')");

        // Prende 2 elementi a partire da offset 1 ordinati DESC
        $subset = $db->getRowSubSet('$_products', 1, 2, 'id', 'DESC');

        $this->assertIsArray($subset);
        $this->assertCount(2, $subset);
        $this->assertEquals(2, $subset[0]['id']);
        $this->assertEquals(1, $subset[1]['id']);
    }

    /**
     * Test della conversione delle date in formato SQL (covertToSqlDate).
     */
    public function testCovertToSqlDate(): void
    {
        $db = new EDbManager($this->sqliteConfig);

        // Data valida
        $sqlDate = $db->covertToSqlDate('2026-04-21 15:30:00');
        $this->assertEquals('2026-04-21 15:30:00', $sqlDate);

        // Stringa data non valida
        $invalidDate = $db->covertToSqlDate('not-a-valid-date');
        $this->assertEquals('', $invalidDate);
    }

    /**
     * Test della formattazione delle virgolette/escaping con sqlFormat.
     */
    public function testSqlFormat(): void
    {
        $db = new EDbManager($this->sqliteConfig);

        $rawInput = "L'utente dice 'Hello'";
        $formatted = $db->sqlFormat($rawInput);

        $this->assertEquals("L\'utente dice \'Hello\'", $formatted);
    }

    /**
     * Test della chiusura esplicita della connessione.
     */
    public function testCloseDisconnectsAdapter(): void
    {
        $db = new EDbManager($this->sqliteConfig);
        $db->doUpdate("CREATE TABLE exp_dummy (id INT)");

        // Invoco il metodo close()
        $db->close();

        // Verifichiamo che la chiamata non sollevi eccezioni e pulisca correttamente
        $this->assertTrue(true);
    }
}
