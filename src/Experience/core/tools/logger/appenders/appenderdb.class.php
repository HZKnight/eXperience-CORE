<?php
    /*
     * appenderdb.class.php
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

     namespace Experience\Core\Tools\Logger\Appenders;


     use Experience\Core\Tools\Logger\Appenders\Appender;
     use Experience\Core\Tools\Logger\ELogRow;
     use Experience\Core\Tools\Config\EConfigManager;

     use Experience\Core\Io\Dbal\EDbManager;


    /**
     * DB appender per ELogger
     *
     * @author  lucliscio <lucliscio@h0model.org>
     * @version 1.0.0
     * @copyright &copy;2026 HZKnight
     * @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
     *
     * @package eXperience
     * @subpackage Core\Tools\Logger\Appenders
     *
     * @filesource
     */

     class AppenderDb extends Appender {

          private string $logname;
          private string $error;

          private EDbManager $db;

          /**
           * Construntor method
           *
           * @param string $logname log name
           * @param EConfigManager $cfg config manager
           */
          public function __construct(string $logname, EConfigManager $cfg){
               $this->logname = $logname;
               $this->error = '';
               $this->db = new EDbManager($cfg);
               parent::__construct($cfg);
          }


          /**
           * Add log row
           *
           * @param ELogRow $log_row log row to add
           * @return bool
           */
          public function add(ELogRow $log_row): bool{
               $this->error = '';

               if($this->createLogger()) {
                    $this->error = $this->db->getError();
                    return false;
               }

               if($log_row->type < $this->loglevel) {
                    return true;
               }

               return $this->insertLogRow($log_row);
          }


          /**
           * Get log rows
           *
           * @param int $start start index
           * @param int $stop stop index
           * @return array
           */
          public function getLog(int $start, int $stop): array{
               $this->error = '';

               return $this->db->getRowSubSet($this->cfg->getParam("db_prefix")."logger", $start, $stop, "created_at", "DESC");
          }


          private function insertLogRow(ELogRow $log_row): bool{
               $logger_id = $this->getLoggerId();
               if($logger_id === null) {
                    return false;
               }

               $sql = "INSERT INTO `".$this->cfg->getParam("db_prefix")."logger` (`logger_id`, `level`, `type`, `message`, `context`, `created_at`) VALUES (?, ?, ?, ?, ?, ?)";
               $params = [
                    1 => $logger_id,
                    2 => $log_row->type,
                    3 => $log_row->type,
                    4 => $log_row->message,
                    5 => '',
                    6 => $log_row->date
               ];
               $res = $this->db->doUpdate($sql, $params);
               if(!$res) {
                    $this->error = $this->db->getError();
                    return false;
               }

               return true;
          }


          private function getLoggerId(): ?int{
               $sql = "SELECT * FROM `".$this->cfg->getParam("db_prefix")."logger` WHERE `name` = ?";
               $result = $this->db->doQuery($sql, [1 => $this->logname]);
               if(!$result || $this->db->getError() != "") {
                    $this->error = $this->db->getError();
                    return null;
               }

               if(empty($result)) {
                    $this->error = "Logger not found and cannot be created";
                    return null;
               }

               return (int) $result[0]['id'];
          }



          private function createLogger(): bool{
               $this->error = '';
               $sql = "SELECT * FROM `".$this->cfg->getParam("db_prefix")."logger` WHERE `name` = ?";
               $result = $this->db->doQuery($sql, [1 => $this->logname]);

               if($result && $this->db->getError()=="") {
                    if(empty($result)) {
                         $sql = "INSERT INTO `".$this->cfg->getParam("db_prefix")."logger` (`name`) VALUES (?)";
                         $res = $this->db->doUpdate($sql, [1 => $this->logname]);
                         if($res && $this->db->getError()=="") {
                              return true;
                         } else {
                              $this->error = $this->db->getError();
                         }
                    } else {
                         return true;
                    }
               } else {
                    $this->error = $this->db->getError();
               }

               return false;
          }

     }
