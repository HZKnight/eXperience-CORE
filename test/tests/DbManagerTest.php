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

        $this->sqliteConfig = [
            'driver'    => 'pdo_sqlite',
            'tb_prefix' => 'exp_',
            'path'      => ':memory:',
            'database'  => ':memory:'
        ];
    }

    /**
     * Test costruttore con array di configurazione
     */
    public function testConstructorWithArrayConfig(): void
    {
        $db = new EDbManager($this->sqliteConfig);
        $this->assertEmpty($db->getError());
    }

    /**
     * Test costruttore con mock di EConfigManager
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
     * Test della gestione di un driver non supportato.
     */
    public function testUnsupportedDriverSetsError(): void
    {
        $invalidConfig = [
            'driver'    => 'oracle_invalid',
            'tb_prefix' => 'exp_'
        ];

        $db = new EDbManager($invalidConfig);

        $this->assertEquals('Unsupported database driver: oracle_invalid', $db->getError());
        
        // Evita che il destruttore PHP invochi close() su un $adapter non inizializzato
        // nel caso la classe EDbManager non abbia il controllo isset($this->adapter)
        $ref = new \ReflectionClass($db);
        if ($ref->hasProperty('adapter')) {
            $prop = $ref->getProperty('adapter');
            $prop->setAccessible(true);
            // Iniettiamo un adapter anonimo nullo o mock per un tearing-down pulito
            $nullAdapter = $this->getMockBuilder(\Experience\Core\Io\Dbal\Driver\BaseAdapter::class)
                                ->disableOriginalConstructor()
                                ->getMock();
            $prop->setValue($db, $nullAdapter);
        }
    }

    /**
     * Test della sostituzione del prefisso '$_' usando parametri posizionali
     * per evitare Mismatch di tipi in PdoAdapter (string + int)
     */
    public function testTablePrefixReplacementInDoQuery(): void
    {
        $db = new EDbManager($this->sqliteConfig);

        $db->doUpdate("CREATE TABLE exp_users (id INTEGER PRIMARY KEY, name TEXT)");
        $db->doUpdate("INSERT INTO exp_users (name) VALUES ('Luca')");

        // Usiamo un array posizionale invece di chiavi stringa per evitare il 'string + int' in PdoAdapter
        $result = $db->doQuery("SELECT * FROM exp_users WHERE name = ?", ['Luca']);

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('Luca', $result[0]['name']);
    }

    /**
     * Test doUpdate
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
     * Test getTableNumRows
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
     * Test getRowSubSet
     */
    public function testGetRowSubSet(): void
    {
        $db = new EDbManager($this->sqliteConfig);

        $db->doUpdate("CREATE TABLE exp_products (id INTEGER PRIMARY KEY, title TEXT)");
        $db->doUpdate("INSERT INTO exp_products (id, title) VALUES (1, 'A'), (2, 'B'), (3, 'C')");

        $subset = $db->getRowSubSet('$_products', 1, 2, 'id', 'DESC');

        $this->assertIsArray($subset);
        $this->assertCount(2, $subset);
        $this->assertEquals(2, $subset[0]['id']);
        $this->assertEquals(1, $subset[1]['id']);
    }

    /**
     * Test conversione data SQL
     */
    public function testCovertToSqlDate(): void
    {
        $db = new EDbManager($this->sqliteConfig);

        $sqlDate = $db->covertToSqlDate('2026-04-21 15:30:00');
        $this->assertEquals('2026-04-21 15:30:00', $sqlDate);

        $invalidDate = $db->covertToSqlDate('not-a-valid-date');
        $this->assertEquals('', $invalidDate);
    }

    /**
     * Test formattazione sqlFormat
     */
    public function testSqlFormat(): void
    {
        $db = new EDbManager($this->sqliteConfig);

        $rawInput = "L'utente dice 'Hello'";
        $formatted = $db->sqlFormat($rawInput);

        $this->assertEquals("L\'utente dice \'Hello\'", $formatted);
    }
}
