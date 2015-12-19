<?php

	namespace HZSystem\Core\Logger\Appenders;
        
        use HZSystem\Core\Logger\HZLogger;
        use HZSystem\Core\Logger\HZLogRow;
	
	require ('../libs/FirePHPCore/FirePHP.class.php');
	
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
         *  FirBUG appender per HZLogger 
         *
         *  @author  Luca Liscio <hzkight@h0model.org>
         *  @version 0.0.1 2015/12/16 06:07:20
         *  @copyright 2015 Luca Liscio
         *  @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
         *
         *  @package HZSystem
         *  @subpackage Core\Logger\Appenders
         *  @filesource
         */
        
	class Appender_firephp extends Appender {
		
		public function add(HZLogRow $log_row){
                    
                    $firephp = FirePHP::getInstance(true);
                    
                    switch($log_row->type){
                        case HZLogger::LOG_FATAL :
                            $firephp->fb("["+$log_row->date+"] "+$log_row->message,FirePHP::ERROR);
                            break;
                        case HZLogger::LOG_ERROR :
                            $firephp->fb("["+$log_row->date+"] "+$log_row->message,FirePHP::ERROR);
                            break;
                        case HZLogger::LOG_WARNING :
                            $firephp->fb("["+$log_row->date+"] "+$log_row->message,FirePHP::WARN);
                            break;
                        case HZLogger::LOG_INFO :
                            $firephp->fb("["+$log_row->date+"] "+$log_row->message,FirePHP::INFO);
                            break;
                        case HZLogger::LOG_DEBUG :
                            $firephp->fb("["+$log_row->date+"] "+$log_row->message,FirePHP::LOG);
                            break;
                    }
                    
		}
		
		public function get_log($start=0,$stop){
                    
                    return NULL;
                    
		}
		
	}