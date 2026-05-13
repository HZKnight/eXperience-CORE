<?php
    /*
     * MySqliAdapter.class.php
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

    use function is_array;
    use function is_int;
    use function is_double;
    use function is_resource;


    /**
     * Adapter per MySqli
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
    class MySqliAdapter extends BaseAdapter implements DatabaseAdapterInterface {

        private const DEFAULT_HOST = 'localhost';
        private const DEFAULT_PORT = 3306;
        private const NO_CONNECTION_ERROR = 'No database connection';

        private \mysqli $connection;
        private string $error = '';
        private array $connData;
        private string $tbprefix;
        private EConfigManager|array $config;
        private int $transactionCounter = 0;


        /**
         * All'atto della costruziine di un nuovo ogetto esegue la connessione al DB
         *
         * @param EConfigManager|array $config
         * @return void
         */
        public function __construct(EConfigManager|array $config) {
            $this->connData = array();
            $this->config = $config;
            $this->error = '';

             if (is_array($config)) {
                // Se passato array, usa direttamente
                $this->connData['host'] = $config['host'] ?? self::DEFAULT_HOST;
                $this->connData['port'] = $config['port'] ?? self::DEFAULT_PORT;
                $this->connData['uname'] = $config['uname'];
                $this->connData['passwd'] = $config['passwd'];
                $this->tbprefix = $config['tb_prefix'];
                $this->connData['db'] = $config['db'] ?? '';
            } else {
                // Usa EConfigManager
                $this->connData['host'] = $config->getParam('db.host', self::DEFAULT_HOST);
                $this->connData['port'] = $config->getParam('db.port', self::DEFAULT_PORT);
                $this->connData['uname'] = $config->getParam('db.uname', '');
                $this->connData['passwd'] = $config->getParam('db.passwd', '');
                $this->tbprefix = $config->getParam('db.tb_prefix', '');
                $this->connData['db'] = $config->getParam('db.db', '');
            }
        }


        /**
         * Esegue la connessione al database
         *
         * @return bool
         */
        public function connect(): bool {
            if (isset($this->connection)) {
                return true; // Se già connesso, restituisci true
            }

            mysqli_report(\MYSQLI_REPORT_ERROR | \MYSQLI_REPORT_STRICT);
            try {
                $this->connection = new \mysqli(
                    $this->connData['host'],
                    $this->connData['uname'],
                    $this->connData['passwd'],
                    $this->connData['db'] ?? '', // Se il database non è specificato, usa stringa vuota
                    $this->connData['port']
                );
            } catch (\mysqli_sql_exception $e) {
                $this->error = $e->getMessage();
                return false;
            }

            return true;
        }

        /**
         * Metodo centrale per l'esecuzione di query con prepared statements
         *
         * @param string $sql La query da eseguire
         * @param array $params I parametri da bindare alla query
         * @return \mysqli_stmt La statement eseguita, da cui è possibile ottenere risultati o il numero di righe interessate
         */
        private function doexecute(string $sql, ?array $params = []): \mysqli_stmt {
            $stmt = $this->connection->prepare($sql);
                
            if (!empty($params)) {
                $this->bindDynamicParams($stmt, $params);
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
                return $stmt->affected_rows;
            } catch (\Exception $e) {
                $this->error = !$this->connection ? self::NO_CONNECTION_ERROR . $e->getMessage() : $e->getMessage();
                return false;
            }
        }


        /**
         * Esegue una query di selezione e restituisce tutte le righe risultanti come array associativo
         * Nota: se la query non restituisce risultati in questo caso restituendo un array vuoto
         *
         * @param string $sql
         * @param array $params
         * @return array
         */
        public function fetchAll(string $sql, ?array $params = []): ?array {
            try {
                $stmt = $this->doexecute($sql, $params);
                $result = $stmt->get_result();
            
                if ($result === false){
                    return null;
                }
                
                $data = $result->fetch_all(MYSQLI_ASSOC);
                $result->free();

                return $data;
            } catch (\Exception $e) {
                $this->error = !$this->connection ? self::NO_CONNECTION_ERROR . $e->getMessage() : $e->getMessage();
                return null;
            }
        }


        /**
         * Esegue una query di selezione e restituisce la prima riga risultante come array associativo
         * Nota: mysqli non supporta direttamente il fetchOne, quindi utilizziamo get_result e fetch_assoc per ottenere la prima riga
         * Nota: se la query non restituisce risultati, get_result potrebbe restituire false, quindi gestiamo questo caso restituendo null
         * Nota: se la query restituisce risultati, ma non ci sono righe, fetch_assoc restituirà null, quindi gestiamo anche questo caso
         * restituendo null
         *
         * @param string $sql
         * @param array $params
         * @return null|array
         */
        public function fetchOne(string $sql, ?array $params = []): ?array {
            try {
                $stmt = $this->doexecute($sql, $params);
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $result->free();

                return $row ?: null;
            } catch (\Exception $e) {
                $this->error = !$this->connection ? self::NO_CONNECTION_ERROR . $e->getMessage() : $e->getMessage();
                return null;
            }
        }


        /**
         * Esegue una query di selezione e restituisce il valore della prima colonna della prima riga risultanteriga
         * Nota: se la query non restituisce risultati,gestiamo questo caso restituendo null
         *
         * @param string $sql
         * @param array $params
         * @return mixed
         */
        public function fetchColumn(string $sql, ?array $params = [], int $columnOffset = 0): mixed {
            try {
                $stmt = $this->doexecute($sql, $params);
                $result = $stmt->get_result();
                $row = $result->fetch_array(MYSQLI_NUM); // Recuperiamo come array numerico
                $result->free();

                return ($row && isset($row[$columnOffset])) ? $row[$columnOffset] : null;
            } catch (\Exception $e) {
                $this->error = !$this->connection ? self::NO_CONNECTION_ERROR . $e->getMessage() : $e->getMessage();
                return null;
            }
        }


        /**
         * Restituisce l'ID dell'ultima riga inserita
         *
         * @return int|string
         */
        public function lastInsertId() {
            try {
                if (!$this->connection) {
                    $this->error = self::NO_CONNECTION_ERROR;
                    return null;
                }
                return $this->connection->insert_id;
            } catch (\Exception $e) {
                $this->error = self::NO_CONNECTION_ERROR . $e->getMessage();
                return null;
            }
        }


        /**
         * Gestisce le transazioni in modo ricorsivo, supportando interazioni tramite savepoint per consentire transazioni annidate
         * Nota: manteniamo un contatore delle transazioni per sapere quando avviare una nuova transazione o creare un savepoint, e per gestire correttamente commit e rollBack in base al livello di annidamento
         *
         * @return void
         */
        public function beginTransaction() {
            $this->connect();
            if ($this->transactionCounter === 0) {
                $this->connection->begin_transaction();
            } else {
                $this->connection->query("SAVEPOINT trans_{$this->transactionCounter}");
            }
            $this->transactionCounter++;
        }


        /**
         * Gestisce il commit delle transazioni, rilasciando i savepoint se ci sono transazioni annidate, o eseguendo il commit completo se siamo al livello più esterno
         * Nota: se ci sono transazioni annidate, invece di eseguire un commit completo, rilasciamo il savepoint corrispondente al livello di annidamento attuale, in modo da consentire alle transazioni esterne di continuare a gestire il commit o il rollBack
         *
         * @return void
         */
        public function commit() {
            if ($this->transactionCounter > 0) {
                $this->transactionCounter--;
                if ($this->transactionCounter === 0) {
                    $this->connection->commit();
                } else {
                    $this->connection->query("RELEASE SAVEPOINT trans_{$this->transactionCounter}");
                }
            }
        }


        /**
         * Gestisce il rollBack delle transazioni, eseguendo un rollBack completo se siamo al livello più esterno, o tornando al save
         * point se ci sono transazioni annidate, in modo da consentire alle transazioni esterne di continuare a gestire il commit o il rollBack
         *
         * @return void
         */
        public function rollBack() {
            if ($this->transactionCounter > 0) {
                $this->transactionCounter--;
                if ($this->transactionCounter === 0) {
                    $this->connection->rollback();
                } else {
                    $this->connection->query("ROLLBACK TO SAVEPOINT trans_" . $this->transactionCounter);
                }
            }
        }


        /**
         * Chiude la connessione al database, se esistente, e pulisce le risorse associate
         *
         * @return void
         */
        public function disconnect() {
            if (isset($this->connection)) {
                $this->connection->close();
                unset($this->connection);
            }
        }


        /**
         * Gestisce il binding dei parametri in modo dinamico
         * Determina i tipi dei parametri e li lega alla query preparata
         *
         * @param \mysqli_stmt $stmt La query preparata
         * @param array $params I parametri da bindare
         * @return void
         */
        private function bindDynamicParams($stmt, array $params) {
            $types = "";
            foreach ($params as $param) {
                if (is_int($param)) {
                    $types .= "i";
                } elseif (is_double($param)) {
                    $types .= "d";
                } elseif (is_resource($param)) {
                    $types .= "b";
                } else {
                    $types .= "s";
                }
            }

            $stmt->bind_param($types, ...$params);
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
