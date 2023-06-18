<?php

    /* 
     * appender_db.class.php
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
     * Copyright (C)2022 HZKnight
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

     namespace Experience\Core\Logger\Appenders;

     use Experience\Core\Exceptions\ENotApplicableMethodException;
     use Experience\Core\Logger\Exceptions\LogFileNotFoundException;
        
     use Experience\Core\Logger\ELogger;
     use Experience\Core\Logger\ELogRow;
     use Experience\Core\Config\EConfigManager;
     use Experience\Core\Database\EDbManager;

     
    /**
     * DB appender per ELogger 
     * 
     * @author  lucliscio <lucliscio@h0model.org>
     * @version v 1.0 2022/10/19 12:38:20
     * @copyright Copyright 2022 HZKnight 
     * @copyright Copyright 2013 Luca Liscio & Marco Lettieri 
     * @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
     *   
     * @package Experience
     * @subpackage Core\Database
     * @filesource
     */
	
	class Appender_db extends Appender {

          /**
           * Construntor method
           * 
           * @param String $logname log name
           */
          public function __construct($logname, EConfigManager $cfg){
               parent::__construct($cfg);
          }
		
		public function add(ELogRow $log_row){
               if($log_row->type >= $this->loglevel){}
		}
		
		public function get_log($start=0,$stop){
			
		}
		
	}