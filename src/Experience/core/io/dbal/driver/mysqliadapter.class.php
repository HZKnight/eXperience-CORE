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
    class MySqliAdapter implements DatabaseAdapterInterface {

        private const DEFAULT_HOST = 'localhost';
        private const DEFAULT_PORT = 3306;

        private \mysqli $connection = null;
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
            $this->error = null;

             if (is_array($config)) {
                // Se passato array, usa direttamente
                $this->connData['host'] = $config['host'] ?? self::DEFAULT_HOST;
                $this->connData['port'] = $config['port'] ?? self::DEFAULT_PORT;
                $this->connData['uname'] = $config['uname'];
                $this->connData['passwd'] = $config['passwd'];
                $this->tbprefix = $config['tb_prefix'];
            } else {
                // Usa EConfigManager
                $this->connData['host'] = $config->getParam('db.host') ?? self::DEFAULT_HOST;
                $this->connData['port'] = $config->getParam('db.port') ?? self::DEFAULT_PORT;
                $this->connData['uname'] = $config->getParam('db.uname');
                $this->connData['passwd'] = $config->getParam('db.passwd');
                $this->tbprefix = $config->getParam('db.tb_prefix');
            }
        }

        
        /**
         * Distruttore dell'adapter PDO, chiude la connessione al database
         */
        public function __destruct() {
            $this->disconnect();
         }


        /**
         * Esegue la connessione al database
         *
         * @return bool
         */
        public function connect(): bool {
            if ($this->connection) {
                return true; // Se già connesso, restituisci true
            }

            mysqli_report(\MYSQLI_REPORT_ERROR | \MYSQLI_REPORT_STRICT);

            try {
                $this->connection = new \mysqli(
                    $this->connData['host'],
                    $this->connData['uname'],
                    $this->connData['passwd'],
                    '', // Database name can be selected later
                    $this->connData['port']
                );
            } catch (\mysqli_sql_exception $e) {
                $this->error = $e->getMessage();
                return false;
            }

            return true;
        }


        /**
         * Esegue un query sql e restituisce il risultato
         *
         * @param string $sql La query da eseguire
         * @param array $params I parametri da bindare alla query
         * @return int|false Il numero di righe interessate o false in caso di errore
         */
        public function execute(string $sql, array $params = []): int|false {
            try {
                $stmt = $this->connection->prepare($sql);
            
                if (!empty($params)) {
                    $this->bindDynamicParams($stmt, $params);
                }

                $stmt->execute();
                return $stmt->affected_rows;
            } catch (\Exception $e) {
                $this->error = !$this->connection ? 'No database connection '. $e->getMessage() : $e->getMessage();
                return false;
            }
        }


        /**
         * Esegue una query di selezione e restituisce tutte le righe risultanti come array associativo
         * Nota: mysqli non supporta direttamente il fetchAll, quindi utilizziamo get_result e fetch_all per ottenere i dati
         * Nota: se la query non restituisce risultati, get_result potrebbe restituire false, quindi gestiamo questo caso 
         * restituendo un array vuoto
         *
         * @param string $sql
         * @param array $params
         * @return array
         */
        public function fetchAll(string $sql, array $params = []): array {
            try {
                $stmt = $this->connection->prepare($sql);
            
                if (!empty($params)) {
                    $this->bindDynamicParams($stmt, $params);
                }

                $stmt->execute();
                $result = $stmt->get_result();
            
                if ($result === false){
                    return [];
                }
                
                $data = $result->fetch_all(MYSQLI_ASSOC);
                $result->free();
                return $data;
            } catch (\Exception $e) {
                $this->error = !$this->connection ? 'No database connection '. $e->getMessage() : $e->getMessage();
                return [];
            }
        }

        public function fetchOne(string $sql, array $params = []): ?array {
            // FetchOne implementation
            return null;
        }

        public function fetchColumn(string $sql, array $params = []): mixed {
            // FetchColumn implementation
            return null;
        }

        public function lastInsertId() {
            // LastInsertId implementation
            return null;
        }

        public function beginTransaction() {
            // BeginTransaction implementation
        }

        public function commit() {
            // Commit implementation
        }

        public function rollBack() {
            // RollBack implementation
        }

        public function disconnect() {
            // Disconnect implementation
        }


        /**
         * Gestisce il binding dei parametri in modo dinamico
         * Determina i tipi dei parametri e li lega alla query preparata
         * Nota: mysqli richiede di specificare i tipi dei parametri (i, d, s, b) e di passarli come argomenti separati
         * Il metodo accetta un array di parametri e costruisce la stringa dei tipi corrispondente, quindi utilizza l'operatore
         * spread per passare i parametri a bind_param
         * Nota: se si inviano BLOB tramite risorsa, mysqli_stmt_send_long_data andrebbe gestito separatamente, ma per semplicità
         * in questo esempio consideriamo solo i tipi base
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
    }
