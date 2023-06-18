<?php

     /*
      *                                        _____                      _                     
      *                                       | ____|_  ___ __   ___ _ __(_) ___ _ __   ___ ___ 
      *                                       |  _| \ \/ / '_ \ / _ \ '__| |/ _ \ '_ \ / __/ _ \
      *                                       | |___ >  <| |_) |  __/ |  | |  __/ | | | (_|  __/
      *                                       |_____/_/\_\ .__/ \___|_|  |_|\___|_| |_|\___\___|
      *                                                  |_| HZKnight free PHP Scripts 
      *
      *                                             lucliscio <lucliscio@h0model.org>, ITALY
      * 
      * -------------------------------------------------------------------------------------------
      * Licence
      * -------------------------------------------------------------------------------------------
      *
      * Copyright (C) 2023 HZKnight
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

     namespace Experience\Core\Logger\Appenders;
     
     use Experience\Core\Exceptions\ENotApplicableMethodException;
     use Experience\Core\Logger\Exceptions\LogFileNotFoundException;
        
     use Experience\Core\Logger\ELogger;
     use Experience\Core\Logger\ELogRow;
     use Experience\Core\Config\EConfigManager;
     use Experience\Core\Net\Mailer\EMailer;
     use Experience\Core\Net\Mailer\EMessage;
     
    /**
     *  Mail appender per ELogger 
     *
     *  @author  Luca Liscio <lucliscio@h0model.org>
     *  @version 0.0.2 2020/11/29 21:16:20
     *  @copyright 2023 HZKnight
     *  @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
     *
     *  @package Experience
     *  @subpackage Core\Logger\Appenders
     *  @filesource
     */
	class Appender_email extends Appender {

          /**
           * Construntor method
           * 
           * @param String $logname log name
           */
          public function __construct($logname, EConfigManager $cfg){
               parent::__construct($cfg);
          }
		
	     /**
           * Send mail width log row
           * 
           * @param ELogRow $log_row
           */
		public function add(ELogRow $log_row){

               if($log_row->type >= $this->loglevel){
                    $message = new EMessage();
                    $errIdentity = self::$error_identifier[$log_row->type];
                    $message->setSubject("[LOGGER NOTIFY: ".$errIdentity."] - $log_row->date");
                    
                    $body = " 
                    ------------------------------------------------------------------------------

                    Sito:\t\t{$this->_cfg->get_param('site_name')} ({$_SERVER['SERVER_NAME']})

                    Tipo log:\t$errIdentity

                    Accaduto il:\t$log_row->date

                    Messaggio:\t$log_row->message

                    ------------------------------------------------------------------------------";

                    $message->setBody($body);
                    $message->addAddres($this->_cfg->get_param('admin_email'));
                    
                    $mailer = new EMailer($this->_cfg);
                    $result = $mailer->send($message);
                    
                    if($result != ""){
                         throw new \Exception($result);
                    }

               }			

		}
		
		/**
           * Return a part of log file
           * 
           * @param integer $start start row
           * @param integer $stop end row
           * @return list of log row
           * @throws LogFileNotFoundException 
           */
		public function get_log($start=0,$stop){
			throw new ENotApplicableMethodException(dgettext("Elang","Method not applicable"));	
		}
		
	}