<?php
    /*
     * DatabaseAdapterInterface.class.php
     *
     *                                         __  __                _
     *                                      ___\ \/ /_ __   ___ _ __(_) ___ _ __   ___ ___
     *                                     / _ \\  /| '_ \ / _ \ '__| |/ _ \ '_ \ / __/ _ \
     *                                    |  __//  \| |_) |  __/ |  | |  __/ | | | (_|  __/
     *                                     \___/_/\_\ .__/ \___|_|  |_|\___|_| |_|\___\___|
     *                                              |_| HZKnight free PHP Scripts
     *
     *                                           lucliscio <lucliscio@h0model.org>, ITALY
     *
     * CORE Ver.1.0.0
     *
     * -------------------------------------------------------------------------------------------
     * License
     * -------------------------------------------------------------------------------------------
     * Copyright (C)2026 HZKnight
     *
     * This program is free software: you can redistribute it and/or modify
     * it under the terms of the GNU Affero General Public License as published by
     * the Free Software Foundation, either version 3 of the License, or
     * (at your option) any later version.
     *
     * This program is distributed in the hope that it will be useful,
     * but WITHOUT ANY WARRANTY; without even the implied warranty of
     * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     * GNU Affero General Public License for more details.
     *
     * You should have received a copy of the GNU Affero General Public License
     * along with this program.  If not, see <http://www.gnu.org/licenses/agpl-3.0.html>.
     * -------------------------------------------------------------------------------------------
     */

    namespace Experience\Core\Io\Dbal\Driver;

    use Experience\Core\Tools\Config\EConfigManager;
    use Experience\Core\Io\Dbal\Driver\BaseAdapter;
    use Experience\Core\Io\Dbal\Driver\Interface\DatabaseAdapterInterface;

    use \PDO;
    use \PDOException;
    use function is_array;
    use function is_int;
    use function is_bool;
    use function is_null;
    use function is_resource;
    use function explode;


    /**
     * Adapter per PDO
     *
     * @author  lucliscio <lucliscio@h0model.org>
     * @version 1.0.0
     * @copyright &copy;2026 HZKnight
     * @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
     * @package eXperience
     * @subpackage Core\Io\Dbal\Driver
     *
     * @filesource
     */
    class PdoAdapter extends BaseAdapter implements DatabaseAdapterInterface {

        private PDO $pdo;
        private string $error;
        private array $connData;
        private string $tbprefix;
        private EConfigManager|array $config;
        private int $transactionCounter = 0;

        /**
         * Costruttore dell'adapter PDO
         *
         * @param EConfigManager|array $config contiene i parametri (type, host, uname, passwd, db) necessari alla connesione
         * @throws PDOException
         */
        public function __construct(EConfigManager|array $config) {
            $this->connData = array();
            $this->config = $config;
            $this->error = '';

            if (is_array($config)) {
                // Se passato array, usa direttamente
                $this->connData['connstr'] = $this->buildDsn();
                $this->connData['uname'] = $config['uname'];
                $this->connData['passwd'] = $config['passwd'];
                $this->tbprefix = $config['tb_prefix'];
            } else {
                // Usa EConfigManager
                $this->connData['connstr'] = $this->buildDsn();
                $this->connData['uname'] = $config->getParam('db.uname');
                $this->connData['passwd'] = $config->getParam('db.passwd');
                $this->tbprefix = $config->getParam('db.tb_prefix');
            }

        }


        /**
         * Restituisce il messaggio di errore
         *
         * @return string
         */
        public function getError(): ?string {
            return $this->error;
        }

        /**
        * Costruisce il DSN in base al driver selezionato
        */
        private function buildDsn(): string {
            $driver = explode("_", is_array($this->config) ? $this->config['driver'] : $this->config->getParam('db.driver'))[1];
            
            if ($driver === 'sqlite') {
                $path = is_array($this->config) ? $this->config['path'] : $this->config->getParam('db.path');
                return "sqlite:" . $path;
            }

            $host = is_array($this->config) ? ($this->config['host'] ?? 'localhost') : ($this->config->getParam('db.host') ?? 'localhost');
            $db   = is_array($this->config) ? $this->config['db'] : $this->config->getParam('db.db');
            
            $defaultPort = $driver === 'pgsql' ? 5432 : 3306;
            $configPort = is_array($this->config) ? ($this->config['port'] ?? $defaultPort) : ($this->config->getParam('db.port') ?? $defaultPort);
            $port = $configPort;

            return "{$driver}:host={$host};port={$port};dbname={$db};charset=utf8mb4";
        }

        /**
         * Summary of connect
         * @return bool
         */
        public function connect(): bool {
            if (isset($this->pdo)) {
                return true; // Se già connesso, restituisci true
            }

            try {
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];

                $this->pdo = new PDO(
                    $this->connData['connstr'],
                    $this->connData['uname'],
                    $this->connData['passwd'],
                    $options
                );
                return true;
            } catch (PDOException $e) {
                $this->error = $e->getMessage();
                return false;
            }
        }


         /**
         * Metodo centrale per l'esecuzione di query con prepared statements
         * Si occupa di preparare la query, bindare i parametri con i tipi corretti e eseguire la query
         * Restituisce l'oggetto statement per ulteriori operazioni (fetch, rowCount, etc.)
         *
         * @param string $sql La query da eseguire
         * @param array $params I parametri da bindare alla query
         * @return PDOStatement L'oggetto statement risultante dall'esecuzione della query
         */
        private function doexecute(string $sql, ?array $params = []): \PDOStatement {
            $stmt = $this->pdo->prepare($sql);

            // Gestione automatica dei tipi per i parametri
            foreach ($params as $index => $value) {
                $type = PDO::PARAM_STR;
                if (is_int($value)) {$type = PDO::PARAM_INT;}
                if (is_bool($value)) {$type = PDO::PARAM_BOOL;}
                if (is_null($value)) {$type = PDO::PARAM_NULL;}
                if (is_resource($value)) {$type = PDO::PARAM_LOB;} // Gestione BLOB

                $stmt->bindValue($index + 1, $value, $type);
            }

            $stmt->execute();
            return $stmt;
        }


        /**
         * Esegue un query sql e restituisce il risultato
         *
         * @param string $sql La query da eseguire
         * @param array $params I parametri da bindare alla query
         * @return int|false Il numero di righe interessate o false in caso di errore
         */
        public function execute(string $sql, ?array $params = []): int|false {
            try {
                $stmt = $this->doexecute($sql, $params);

                return $stmt->rowCount();
            } catch (PDOException $e) {
                $this->error = $e->getMessage();
                return false;
            }
        }

        /**
         * Esegue una query di selezione e restituisce tutte le righe risultanti
         *
         * @param string $sql La query da eseguire
         * @param array $params I parametri da bindare alla query
         * @return array Un array di righe risultanti
         * @throws PDOException
         */
        public function fetchAll(string $sql, ?array $params = []): ?array {
            try {
                $stmt = $this->doexecute($sql, $params);

                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $this->error = $e->getMessage();
                return null;
            }
        }


        /**
         * Esegue una query di selezione e restituisce la prima riga risultante
         *
         * @param string $sql La query da eseguire
         * @param array $params I parametri da bindare alla query
         * @return array|null La prima riga risultante o null se non ci sono risultati
         * @throws PDOException
         */
        public function fetchOne(string $sql, ?array $params = []): ?array {
            try {
                $stmt = $this->doexecute($sql, $params);

                return $stmt->fetchObject(PDO::FETCH_ASSOC) ?: null;
            } catch (PDOException $e) {
                $this->error = $e->getMessage();
                return null;
            }
        }


        /**
         * Recupera un singolo valore dalla prima riga del risultato della query
         *
         * @param string $sql La query da eseguire
         * @param array $params I parametri da bindare alla query
         * @return mixed Il valore recuperato o null se non ci sono risultati
         * @throws PDOException
         */
        public function fetchColumn(string $sql, ?array $params = [], int $columnOffset = 0): mixed {
            try {
                $stmt = $this->doexecute($sql, $params);

                return $stmt->fetchColumn($columnOffset);
            } catch (PDOException $e) {
                $this->error = $e->getMessage();
                return null;
            }
        }


        /**
         * Restituisce l'ID dell'ultima riga inserita
         *
         * @return string L'ID dell'ultima riga inserita
         */
        public function lastInsertId() {
            try {
                return $this->pdo->lastInsertId();
            } catch (PDOException $e) {
                $this->error = $e->getMessage();
                return null;
            }
        }


        /**
         * Inizia una transazione
         *
         * @return void
         */
        public function beginTransaction() {
            $this->connect();
            if ($this->transactionCounter === 0) {
                $this->pdo->beginTransaction();
            } else {
                $this->pdo->exec("SAVEPOINT trans_{$this->transactionCounter}");
            }
            $this->transactionCounter++;
        }


        /**
         * Commette una transazione
         *
         * @return void
         */
        public function commit() {
            $this->transactionCounter--;
            if ($this->transactionCounter === 0) {
                $this->pdo->commit();
            } else {
                $this->pdo->exec("RELEASE SAVEPOINT trans_{$this->transactionCounter}");
            }
        }


        /**
         * Annulla una transazione
         *
         * @return void
         */
        public function rollBack() {
            if ($this->transactionCounter > 0) {
                $this->transactionCounter--;
                if ($this->transactionCounter === 0) {
                    $this->pdo->rollBack();
                } else {
                    $this->pdo->exec("ROLLBACK TO SAVEPOINT trans_{$this->transactionCounter}");
                }
            }
        }


        /**
         * Chiude la connessione al database
         *
         * @return void
         */
        public function disconnect() {
            unset($this->pdo);
        }
    }
