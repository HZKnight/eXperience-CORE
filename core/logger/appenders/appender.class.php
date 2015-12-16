<?php

	namespace HZSystem\Core\Logger\Appenders;
        
        use HZSystem\Core\Logger\HZLogRow;
	
	/*
	 * Copyright (C) 2015 Luca Liscio
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
	 *  Abstract appender per il Logger di sistema
	 *
	 *  @author  Luca Liscio <hzkight@h0model.org>
	 *  @version 0.0.1 2015/11/30 01:44:20
	 *  @copyright 2015 Luca Liscio
	 *  @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
	 *
	 *  @package HZSystem
	 *  @subpackage Core\Logger\Appenders
	 *  @abstract
	 *  @filesource
	 */

	abstract class Appender {
		
		private $loglevel;
		
		abstract public function add(HZLogRow $log_row);
		abstract public function get_log($start=0,$stop);
		
		public function setLogLevel($level){
		 
			$this->loglevel = $level;
		
		}
		
	}
?>