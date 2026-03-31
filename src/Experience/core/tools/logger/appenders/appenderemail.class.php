<?php
    /*
     * appendermail.class.php
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
     
     use Experience\Core\Exceptions\ENotApplicableMethodException;
     use Experience\Core\Tools\Logger\Exceptions\LogFileNotFoundException;
        
     use Experience\Core\Tools\Logger\Appenders\Appender;
     use Experience\Core\Tools\Logger\ELogRow;
     use Experience\Core\Tools\Config\EConfigManager;
     use Experience\Core\Io\Net\Mailer\EMailer;
     use Experience\Core\Io\Net\Mailer\EMessage;
     
    /**
     * Mail appender per ELogger
     *
     * @author  Luca Liscio <lucliscio@h0model.org>
     * @version 0.0.3
     * @copyright 2020-2025 HZKnight
     * @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
     *
     * @package eXperience
     * @subpackage Core\Tools\Logger\Appenders
     *
     * @filesource
     */
     class AppenderEmail extends Appender {

          private string $logname;

          /**
           * Construntor method
           *
           * @param string $logname log name
           */
          public function __construct($logname, EConfigManager $cfg){
               $this->logname = $logname;
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
                    $errIdentity = self::$errorIdentifier[$log_row->type];
                    $message->setSubject("[LOGGER NOTIFY: ".$errIdentity."] - $log_row->date");
                    
                    $body = "
                    ------------------------------------------------------------------------------

                    Sito:\t\t{$this->cfg->getParam('site_name')} ({$_SERVER['SERVER_NAME']})

                    Tipo log:\t\t$errIdentity

                    Accaduto il:\t$log_row->date

                    Messaggio:\t\t$log_row->message

                    ------------------------------------------------------------------------------";

                    $message->setBody($body);
                    $message->addAddres($this->cfg->getParam('admin_email'));
                    
                    $mailer = new EMailer($this->cfg, null);
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
          public function getLog($start,$stop){
               throw new ENotApplicableMethodException(dgettext("Elang","Method not applicable"));
          }

     }
