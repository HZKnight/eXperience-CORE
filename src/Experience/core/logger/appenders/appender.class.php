<?php

	namespace Experience\Core\Logger\Appenders;
        
    use Experience\Core\Logger\ELogRow;
	
	/*
	 * Copyright (C) 2020 HZKnight
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
	 */
	
	/**
	 *  Abstract appender per ELogger
	 *
	 *  @author  Luca Liscio <lucliscio@h0model.org>
	 *  @version 0.0.3 2020/12/03 22:41:20
	 *  @copyright 2020 HZKnight
	 *  @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
	 *
	 *  @package Experience
	 *  @subpackage Core\Logger\Appenders
	 *  @abstract
	 *  @filesource
	 */

	abstract class Appender {
		
		public $loglevel;
        static $error_identifier;
                
        public function __construct() {

			/*
				const EMERGENCY = 407;
				const ALERT     = 406;
				const CRITICAL  = 405;
				const ERROR     = 404;
				const WARNING   = 403;
				const NOTICE    = 402;
				const INFO      = 401;
				const DEBUG     = 400;
			*/

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
        }

        abstract public function add(ELogRow $log_row);
		abstract public function get_log($start=0,$stop);
		
		public function setLogLevel($level){
		 
            $this->loglevel = $level;
		
		}
		
	}
?>