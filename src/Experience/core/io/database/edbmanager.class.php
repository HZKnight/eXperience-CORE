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
     * Copyright (C)2025 HZKnight
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

    namespace Experience\Core\Io\Database;
    
    use Experience\Core\Tools\Config\EConfigManager;

    /**
     * Interfaccia di comunicazione con il db (Database type MySql-PDO)
     *
     * @author  lucliscio <lucliscio@h0model.org>
     * @version 3.1.0-PDO
     * @copyright Copyright 2022-2025 HZKnight
     * @copyright Copyright 2013 Luca Liscio & Marco Lettieri
     * @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
     *
     * @package eXperience
     * @subpackage Core\Io\Database
     *
     * @filesource
     */

    class EDbManager {

        const VERSION = '3.1.0-PDO';
        const DATE_APPROVED = '2025-02-18';

        private mixed $conn;
        private string $tbprefix;
        private array $connData;
        private mixed $error;

        /**
         * All'atto della costruziine di un nuovo ogetto esegue la connessione al DB
         *
         * @param array $config contiene i parametri (type, host, uname, passwd, db) necessari alla connesione
         * @throws PDOException
         */
        public function __construct(EConfigManager $config) {
            $this->connData = array();
            $this->connData['connstr'] = $config->get_param('db.type').":host=".$config->get_param('db.host').";port=".$config->get_param('db.port').";dbname=".$config->get_param('db.table').";charset=utf8";
            $this->connData['uname'] = $config->get_param('db.uname');
            $this->connData['passwd'] = $config->get_param('db.passwd');
            $this->tbprefix = $config->get_param('db.tb_prefix');
            $this->error = null;
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
         * @return array $res contiene il resultset
         */
        public function doQuery($sql){
            //Send a sql query that returns a result
            $sql = str_replace('$_', $this->tbprefix, $sql);

            if($this->connect()){
                $stmt = $this->conn->query($sql);
                $this->close();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            return null;
        }

        /**
         * Invia al db query di tipo comando e restituisce l'esito dell'esecuzione
         *
         * @param string $sql
         * @return array restituisce l'esito della query
         */
        public function doUpdate($sql){
            //Send a sql command that returns the number of rows affected
            $sql = str_replace('$_', $this->tbprefix, $sql);

            if($this->connect()){
                $af = $this->conn->exec($sql);
                $this->error = $this->conn->errorInfo()[2];
                $this->close();
                $result["sql"] = $sql;
                $result["nbrows"] = $af;
                $result["error"] = $this->error;
                return $result;
            }

            return null;
        }
    
        /**
         * Restituisce l'ultimo id inserito
         * @return mixed
         */
        public function sqlInsertId(){
            return $this->conn->lastInsertId();
        }

        /**
         * Restituisce il numero di righe di una tebella
         *
         * @param string $table tabella
         * @return int numero di righe della tabella
         */
        public function getTableNumRows($table){
            $sql = 'SELECT COUNT(*) AS "rows" FROM '.$table;
            $num = $this->doQuery($sql);
            return $num[0]['rows'];
        }

        /**
         * Restituisce un sottoinsieme delle righe di una tabella
         *
         * @param string $table
         * @param integer $start
         * @param integer $numrow
         * @param string $order
         * @param string $otype
         * @return resultset
         */
        public function getRowSubSet($table, $start, $numrow, $order="", $otype=""){
            $sql = 'SELECT * FROM '.$table;
            
            // Tipo di ordinamento delle righe
            if($order != ""){
                $sql .= ' ORDER BY '.$order." ".$otype;
            }
            
            $sql .=' LIMIT '.$start.', '.$numrow;

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
            try {
                $this->conn = new PDO($this->connData['connstr'], $this->connData['uname'], $this->connData['passwd']);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                return true;
            } catch (Exception $e){
                $this->error = $e->getMessage();
                return false;
            }
        }

        /**
         * Chiude la connesione con il db
         */
        private function close(){
            $this->conn = null;
        }
    
    }
