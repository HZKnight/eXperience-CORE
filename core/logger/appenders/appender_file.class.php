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
         *  File appender per HZLogger 
         *
         *  @author  Luca Liscio <hzkight@h0model.org>
         *  @version 0.0.1 2015/12/19 19:20:20
         *  @copyright 2015 Luca Liscio
         *  @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
         *
         *  @package HZSystem
         *  @subpackage Core\Logger\Appenders
         *  @filesource
         */
        
	class Appender_file extends Appender {
		
	    private $logfile;
            private $logfile_basename;
            private $logfile_basedir;
		
            public function __construct($logname){
                
                self::parent();
                $this->logfile_basedir = "log";
                $this->logfile_basename = $logname;
                $this->logfile = $this->logfile_basedir."/".$this->logfile_basename."_".date("dmY").".log";
                
            }
		
            public function add(HZLogRow $log_row){
                
                if($log_row->type>=$this->loglevel)
                    error_log("(".$log_row->date.") [".$hils->error_identifier[$log_row->type]."] --> ".$log_row->message."\n",3,$this->logfile);
                
            }
		
            public function get_log($start=0,$stop){
                   
                if (file_exists($this->logfile)) {
                    $log = file($this->logfile);
                    if($stop==null){
                        return array_slice($log, $start);
                    } else {
                        return array_slice($log, $start, $stop-$start);
                    }
                } else {
                    throw new LogFileNotFoundExceprions("File di log non trovato");
                }
                                        
            }
		
	}