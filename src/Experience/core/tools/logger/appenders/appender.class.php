<?php
    /*
     * appender.class.php
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


    namespace Experience\Core\Tools\Logger\Appenders;
    
    use Experience\Core\Tools\Config\EConfigManager;
    use Experience\Core\Tools\Logger\ELogger;
    use Experience\Core\Tools\Logger\ELogRow;

    /**
     * Abstract appender per ELogger
     *
     * @author  Luca Liscio <lucliscio@h0model.org>
     * @version 0.0.5
     * @copyright 2020-2025 HZKnight
     * @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
     *
     * @package eXperience
     * @subpackage Core\Logger\Appenders
     * @abstract
     *
     * @filesource
     */

    abstract class Appender {

        public $loglevel;
        public EConfigManager $cfg;
        static $errorIdentifier;

        public function __construct(EConfigManager $config) {

            self::$error_identifier = array(
                407 => "EMERGENCY",
                406 => "ALERT",
                405 => "CRITICAL",
                404 => "ERROR",
                403 => "WARNING",
                402 => "NOTICE",
                401 => "INFO",
                400 => "DEBUG"
            );

            $this->cfg = $config;
        }

        abstract public function add(ELogRow $log_row);
        abstract public function getLog($start,$stop);

        public function setLogLevel($level){

            $this->loglevel = $level;

        }

    }
