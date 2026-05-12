<?php
    /*
     * SqliteAdapter.class.php
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

    use Experience\Core\Io\Dbal\Driver\BaseAdapter;
    use Experience\Core\Io\Dbal\Driver\Interface\DatabaseAdapterInterface;
    use Experience\Core\Tools\Config\EConfigManager;

    use SQLite3;
    use SQLite3Result;

    use function is_array;
    use function is_int;
    use function is_float;
    use function is_null;
    use function is_resource;


    /**
     * Adapter per database SQLite
     *
     * @author lucliscio <lucliscio@h0model.org>
     * @version 1.0.0
     * @copyright &copy;2026 HZKnight
     * @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
     * @package eXperience
     * @subpackage Core\Io\Dbal\Driver
     *
     * @filesource
     */
    class SqliteAdapter extends BaseAdapter implements DatabaseAdapterInterface {

        private const DEFAULT_CONFIG = [
            'path' => 'database.sqlite',
            'tbprefix' => '',
        ];

        private SQLite3 $connection;
        private string $error;
        private array $connData;
        private string $tbprefix;
        private EConfigManager|array $config;
        private int $transactionCounter = 0;

        
        /**
         * Costruttore dell'adapter. Accetta un'istanza di EConfigManager o un array associativo con i parametri di connessione
         * I parametri accettati sono:
         * - 'path': il percorso del file SQLite
         * - 'tbprefix': il prefisso da utilizzare per le tabelle (opzionale)
         * Se viene passato un array, i parametri devono essere presenti come chiavi. Se viene passato un'istanza di EConfigManager
         * , i parametri vengono letti tramite il metodo getParam con chiavi 'db.path' e 'db.tbprefix'
         *
         * @param EConfigManager|array $config configurazione della connessione
         */
        public function __construct(EConfigManager|array $config) {
            $this->connData = [];
            $this->config = $config;
            $this->error = null;

            if(is_array($config)) {
                $this->connData['path'] = $this->config['path'] ?? self::DEFAULT_CONFIG['path'];
                $this->connData['tbprefix'] = $this->config['tbprefix'] ?? self::DEFAULT_CONFIG['tbprefix'];
            } else {
                $this->connData['path'] = $this->config->getParam('db.path', self::DEFAULT_CONFIG['path']);
                $this->connData['tbprefix'] = $this->config->getParam('db.tbprefix', self::DEFAULT_CONFIG['tbprefix']);
            }
            $this->tbprefix = $this->connData['tbprefix'];
        }


        /**
         * Apre la connessione al file database SQLite
         *
         * @return boolean
         */
        public function connect(): bool {
            if ($this->connection) {
                return true; // Connessione già stabilita
            }

            try {
                // $this->config['path'] deve contenere il percorso del file .db
                $this->connection = new SQLite3($this->config['path']);
                
                // Abilitiamo le eccezioni per errori SQL
                $this->connection->enableExceptions(true);
            } catch (\Exception $e) {
                $this->error = "Errore connessione SQLite: " . $e->getMessage();
                return false;
            }
            return true;
        }


        /**
         * Metodo centrale per l'esecuzione di query con prepared statements
         *
         * @param string $sql La query da eseguire
         * @param array $params I parametri da bindare alla query
         * @return \SQLite3Result Il risultato della query o false in caso di errore
         */
        private function doexecute(string $sql, ?array $params = []): \SQLite3Result {
            $stmt = $this->connection->prepare($sql);

            foreach ($params as $index => $value) {
                // SQLite3 bindValue usa indici 1-based per i placeholder '?'
                $type = SQLITE3_TEXT;
                if (is_int($value)) {
                    $type = SQLITE3_INTEGER;
                }
                if (is_float($value)){
                    $type = SQLITE3_FLOAT;
                }
                if ($value === null){
                    $type = SQLITE3_NULL;
                }
                if (is_resource($value)){
                    $type = SQLITE3_BLOB;
                }

                $stmt->bindValue($index + 1, $value, $type);
            }

            return $stmt->execute(); // Ritorna un oggetto SQLite3Result
        }


        /**
         * Esegue una query SQL con parametri opzionali. Restituisce il numero di righe interessate o false in caso di errore.
         *
         * @param string $sql
         * @param array $params
         * @return integer|false
         */
        public function execute(string $sql, ?array $params = []): int|false {
            try {
                $this->doexecute($sql, $params);

                return $this->connection->changes();
            } catch (\Exception $e) {
                $this->error = "Errore esecuzione query: {$e->getMessage()}";
                return false;
            }
        }


        /**
         * Esegue una query SQL con parametri opzionali e restituisce tutte le righe risultanti come array associativo.
         *
         *
         * @param string $sql
         * @param array $params
         * @return array
         */
        public function fetchAll(string $sql, ?array $params = []): ?array {
            try {
               $result = $this->doexecute($sql, $params);
               $data = [];
                
                // Itera sui risultati dell'oggetto SQLite3Result
                while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                    $data[] = $row;
                }
                
                $result->finalize(); // Libera la memoria del risultato
                return $data;
            } catch (\Exception $e) {
                $this->error = "Errore esecuzione query: {$e->getMessage()}";
                return null;
            }
        }


        /**
         * Recupera una singola riga
         *
         * @param string $sql
         * @param mixed $params
         */
        public function fetchOne(string $sql, ?array $params = []): ?array {
            try {
                $result = $this->doexecute($sql, $params);
                $row = $result->fetchArray(SQLITE3_ASSOC);
                $result->finalize();
        
                return $row ?: null;
            } catch (\Exception $e) {
                $this->error = "Errore esecuzione query: {$e->getMessage()}";
                return null;
            }
        }


        /**
         * Recupera il valore di una singola colonna dalla prima riga del risultato
         *
         * @param string $sql
         * @param array $params
         * @param int $columnOffset l'indice della colonna da recuperare (0-based)
         * @return mixed
         */
        public function fetchColumn(string $sql, ?array $params = [], int $columnOffset = 0): mixed {
            try {
                $result = $this->doexecute($sql, $params);
                $row = $result->fetchArray(SQLITE3_NUM);
                $result->finalize();

                return ($row && isset($row[$columnOffset])) ? $row[$columnOffset] : null;
            } catch (\Exception $e) {
                $this->error = "Errore esecuzione query: {$e->getMessage()}";
                return null;
            }
        }


        /**
         * Restituisce l'ID dell'ultima riga inserita nel database
         *
         * @return mixed
         */
        public function lastInsertId() {
            return $this->connection->lastInsertRowID();
        }


        /**
         * Gestione Transazioni
         * SQLite supporta le transazioni, ma non ha un vero e proprio supporto per le transazioni annidate. Per gestire questo, utilizziamo un contatore di transazioni e i savepoint per simulare le transazioni annidate.
         *
         * @return void
         */
        public function beginTransaction() {
            $this->connect();
            if ($this->transactionCounter === 0) {
                $this->connection->exec("BEGIN TRANSACTION");
            } else {
                $this->connection->exec("SAVEPOINT trans_" . $this->transactionCounter);
            }
            $this->transactionCounter++;
        }


        /**
         * Completa la transazione corrente. Se ci sono transazioni annidate, rilascia il savepoint corrispondente.
         *
         * @return void
         */
        public function commit() {
            if ($this->transactionCounter > 0) {
                $this->transactionCounter--;
                if ($this->transactionCounter === 0) {
                    $this->connection->exec("COMMIT");
                } else {
                    $this->connection->exec("RELEASE SAVEPOINT trans_" . $this->transactionCounter);
                }
            }
        }


        /**
         * Annulla la transazione corrente. Se ci sono transazioni annidate, esegue un rollback al savepoint corrispondente.
         *
         * @return void
         */
        public function rollBack() {
            if ($this->transactionCounter > 0) {
                $this->transactionCounter--;
                if ($this->transactionCounter === 0) {
                    $this->connection->exec("ROLLBACK");
                } else {
                    $this->connection->exec("ROLLBACK TO SAVEPOINT trans_" . $this->transactionCounter);
                }
            }
        }


        /**
         * Chiude la connessione al database
         *
         * @return void
         */
        public function disconnect() {
            if ($this->connection) {
                $this->connection->close();
                unset($this->connection);
            }
        }

        /**
         * Restituisce l'ultimo errore
         *
         * @return string
         */
        public function getError(): ?string {
            return $this->error;
        }
}
