<?php

    /*
     * edbmanager.class.php
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
     * Lincense
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

    namespace Experience\Core\Io\Dbal;
    
    use Experience\Core\Tools\Config\EConfigManager;
    use Experience\Core\Io\Dbal\Driver\BaseAdapter;
    use Experience\Core\Io\Dbal\Driver\SqliteAdapter;
    use Experience\Core\Io\Dbal\Driver\MySqliAdapter;
    use Experience\Core\Io\Dbal\Driver\PdoAdapter;

    use \Exception;

    use function str_replace;
    use function is_array;


    /**
     * Interfaccia di comunicazione con il db (Database type MySql-PDO)
     *
     * @author  lucliscio <lucliscio@h0model.org>
     * @version 4.0.0
     * @copyright Copyright 2022-2026 HZKnight
     * @copyright Copyright 2013 Luca Liscio & Marco Lettieri
     * @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
     *
     * @package eXperience
     * @subpackage Core\Io\Dbal
     *
     * @filesource
     */

    class EDbManager {

        public const string VERSION = '4.0.0';
        public const string DATE_APPROVED = '2026-04-21';

        
        private BaseAdapter $adapter;
        private string $dbtype;
        private string $tbprefix;
        private array $connData;
        private string $error;


        /**
         * All'atto della costruziine di un nuovo ogetto esegue la connessione al DB
         *
         * @param EConfigManager|array $config contiene i parametri (driver, host, uname, passwd, db, path) necessari alla connesione
         */
        public function __construct(EConfigManager|array $config) {

            $this->connData = array();
            $this->dbtype = is_array($config) ? $config['driver'] : $config->getParam('db.driver');
            $this->tbprefix = is_array($config) ? $config['tb_prefix'] : $config->getParam('db.tb_prefix');
            $this->error = '';

            switch($this->dbtype) {
                case 'pdo_mysql':
                    $this->adapter = new PdoAdapter($config);
                    break;
                case 'mysqli':
                    $this->adapter = new MySqliAdapter($config);
                    break;
                case 'sqlite':
                    $this->adapter = new SqliteAdapter($config);
                    break;
                default:
                    $this->error = "Unsupported database driver: {$this->dbtype}";
                    break;
            }

        }


        /**
         * Distruttore dell'oggetto, chiude la connessione al database
         */
        public function __destruct() {
            $this->close();
        }


        /**
         * Restituisce il messaggio di errore
         *
         * @return string
         */
        public function getError():string {
            return $this->error;
        }
        
        /**
         * Esegue un query sql e restituisce il risultato
         *
         * @param string $sql stringa contenente la query
         * @param array|null $params array associativo dei parametri da bindare alla query (opzionale)
         * @return array $res contiene il resultset
         */
        public function doQuery(string $sql, ?array $params = []): ?array {
            //Send a sql query that returns a result
            $sql = str_replace('$_', $this->tbprefix, $sql);

            if (!$this->connect()) {
                $this->error = "Connection failed";
                return null;
            }
            
           
            $result = $this->adapter->fetchAll($sql, $params);
            if($this->adapter->getError()) {
                $this->error = $this->adapter->getError();
                return null;
            }
            return $result;
        }


        /**
         * Invia al db query di tipo comando e restituisce l'esito dell'esecuzione
         *
         * @param string $sql
         * @param array|null $params
         * @return array restituisce l'esito della query
         */
        public function doUpdate(string $sql, ?array $params = []): ?array {
            $sql = str_replace('$_', $this->tbprefix, $sql);
            $result = [
                "sql" => $sql,
                "nbrows" => null
            ];

            if (!$this->connect()) {
                $this->error = "Connection failed";
                return null;
            }
            
            try {
                $af = $this->adapter->execute($sql, $params);
                $result["nbrows"] = $af;
                if($this->adapter->getError()) {
                    $result["error"] = $this->adapter->getError();
                }
            } catch (Exception $e) {
                $result["error"] = $e->getMessage();
                return null;
            }

            return $result;
        }
    

        /**
         * Restituisce l'ultimo id inserito
         * @return mixed
         */
        public function sqlInsertId(){
            return $this->adapter->lastInsertId();
        }


        /**
         * Restituisce il numero di righe di una tebella
         *
         * @param string $table tabella
         * @return int numero di righe della tabella
         */
        public function getTableNumRows(string $table): ?int {
            $table = str_replace('$_', $this->tbprefix, $table);
            $sql = "SELECT COUNT(*) FROM $table";
            return $this->adapter->fetchColumn($sql, [],  0);
        }


        /**
         * Restituisce un sottoinsieme delle righe di una tabella
         *
         * @param string $table
         * @param integer $start
         * @param integer $numrow
         * @param string $order
         * @param string $otype
         * @return array|null
         */
        public function getRowSubSet(string $table, int $start, int $numrow, string $order = "", string $otype = ""): ?array {
            $table = str_replace('$_', $this->tbprefix, $table);
            $sql = "SELECT * FROM $table";
            
            // Tipo di ordinamento delle righe
            if($order != ""){
                $sql .= " ORDER BY {$order} {$otype}";
            }
            
            $sql .= " LIMIT {$start}, {$numrow}";

            echo "Executing SQL: $sql"; // Debug line, can be removed in production

            return $this->doQuery($sql);
        }
        
    
        /**
         * Formater for \' items
         */
        public function sqlFormat($data){
            //When passing the data from a post form, the \' are already set, we will
            //replace them with a system code, then replace the single ' by \' and re
            //replace the old system code with \'
            $formateddata = str_replace("\'", "@sc:bsqu@", $data);
            $formateddata = str_replace("'", "\'", $formateddata);
            $formateddata = str_replace("@sc:bsqu@", "\'", $formateddata);
            return $formateddata;
        }


        //Private methods
        /**
         * Apre la connesione con il db
         */
        private function connect(): bool{
            if ($this->adapter->connect()) {
                return true;
            } else {
                $this->error = $this->adapter->getError();
                return false;
            }
        }


        /**
         * Chiude la connesione con il db
         */
        public function close(){
            $this->adapter->disconnect();
        }
    
    }
