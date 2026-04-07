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


    /**
     * DB appender per ELogger
     *
     * @author  lucliscio <lucliscio@h0model.org>
     * @version 0.0.2
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

          /**
           * Construntor method
           *
           * @param string $logname log name
           * @param EConfigManager $cfg config manager
           */
          public function __construct(string $logname, EConfigManager $cfg){
               $this->logname = $logname;
               parent::__construct($cfg);
          }

          /**
           * Add log row
           *
           * @param ELogRow $log_row log row to add
           * @return int
           */
          public function add(ELogRow $log_row){
               if($log_row->type >= $this->loglevel) {
                    //TODO: implement add log row to database
               }
          }

          /**
           * Get log rows
           *
           * @param int $start start index
           * @param int $stop stop index
           * @return array
           */
          public function getLog(int $start, int $stop): array{
               if(!$stop){
                    if(!$start){
                    $start = 0;
               }$stop = 0;
               }
               return [];
          }

     }
