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
    
    namespace Experience\Core\Logger;
     
    use Experience\Core\Logger\Appenders;
    use Experience\Core\Logger\Appenders\Appender_file;
    use Experience\Core\Logger\Appenders\Appender_email;
    use Experience\Core\Logger\Appenders\Appender_db;
    use Experience\Core\Logger\Appenders\Appender_firephp;
    use Psr\Log\LoggerInterface;
    use Psr\Log\LogLevel;	
	
    /**
     *  Logger di sistema 
     *
     *  @author  Luca Liscio <lucliscio@h0model.org>
     *  @version 2.0.3 2020/12/03 22:41:10
     *  @copyright &copy;2020 HZKnight
     *  @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
     *
     *  @package Experience
     *  @subpackage Core\Logger
     *  @filesource
     */
	
    class ELogger implements LoggerInterface {
        
    	// Appenders type
        /** File type log */
        const LOG_APPENDER_FILE =    501;
        /** Email type log */
 	    const LOG_APPENDER_EMAIL =   502;
 	    /** Data base type log */
 	    const LOG_APPENDER_DB =      503;
 	    /** Firebug type log */
 	    const LOG_APPENDER_FIREPHP = 504;
 		
    	//Dati del logger
 	    private $logname;
    	private $date_format;
        private $appenders = array();
        private $loglevel;
    	
    	//Contiene le istanze del logger
    	private static $_instace = array();
            
        /**
	     * Crea una nuova istanza del logger
         * @param string $logname nome della nuova istanza del logger da creare
	     * @param integer $type tipo di logger da creare 
         * @param integer $loglevel livello di errore da cui cominciare a registrare il log
	     * @return \Experience\Core\Logger\ELogger
	     * @example $miolog = ELogger::gelLogger("miolog",ELogger::LOG_APPENDER_FILE,ELogLevel::INFO);
	     */
        public static function getLogger($logname,$type=self::LOG_APPENDER_FILE, $loglevel=ELogLevel::INFO){
            if(array_key_exists($logname, self::$_instace)) {    
                if (!(self::$_instace[$logname] instanceof self)){
                    self::$_instace[$logname] = new self($logname,$type,$loglevel);
                }
            } else {
                self::$_instace[$logname] = new self($logname,$type,$loglevel); 
            }
            
            return self::$_instace[$logname];
        }
            
        /**
         * Restituisce un array con tutte le istanze del logger
         * @return Experience\Core\Logger\ELogger[]
         */
        public static function getIstances(){
                
            return self::$_instace;
	            
        }
	    
	    /**
         * Metodo costruttore
	     * @param string $logname nome del logger
	     * @param integer $type tipo di logger da creare
         * @param integer $loglevel livello di errore da cui cominciare a registrare il log
	     */ 
	    private function __construct($logname,$type,$loglevel){
	   
            $this->logname = $logname;
            $this->loglevel = $loglevel;
            $this->add_appender($type);
            $this->get_appender($type)->setLogLevel($this->loglevel);
            $this->date_format = "d-m-Y H:i:s";
	      
	    }
            
        /**
	     * Aggiunge un nuovo appender al logger
	     * @param integer $type tipo di appender da aggiungere
	     */
	    public function add_appender($type){
	    	
	        switch ($type){
                case self::LOG_APPENDER_FILE:
                    $this->appenders[$type] = new Appender_file($this->logname);
                    break;
                case self::LOG_APPENDER_EMAIL:
                    $this->appenders[$type] = new Appender_email($this->logname);
                    break;
                case self::LOG_APPENDER_DB:
                    $this->appenders[$type] = new Appender_db($this->logname);
                    break;
                case self::LOG_APPENDER_FIREPHP:
                    $this->appenders[$type] = new Appender_firephp($this->logname);
                    break;	    			
	        }
                
            $this->get_appender($type)->setLogLevel($this->loglevel);
	    	
	    }
	    
        /**
         * Rimuove un appender dal logger
         * @param integer $type tipo di appender da rimuovere
         * @throws AppenderNotFoundException
         */
        public function remove_appender($type){
                
            if(isset($this->appenders[$type])){
                unset($this->appenders[$type]);	
            } else {
                throw new AppenderNotFoundException(dgettext("Elang","Appender requested not found"));
            }
            
        }
	    
        /**
         * restituisce l'appender associato al tipo specifiato
         * @param integer $type tipo di appender che si vuole ottenere
         * @throws AppenderNotFoundException
         * @return Experience\Core\Logger\Appenders\Appender
         */
        public function get_appender($type){
                
            if(isset($this->appenders[$type])){
                return $this->appenders[$type];
            } else {
                throw new AppenderNotFoundException(dgettext("Elang","Appender requested not found"));
            }
                
        }
        
        /**
         * Restituisce la lista degli appenders attivi sotto forma di array 
         * bidimensionale [codice][tipo]
         * @return string
         */
        public function get_appenders_list(){
            
            $list = array();
            
            $keys = array_keys($this->appenders); 
            
            foreach($keys as $key){
                
                switch ($key){
                    case self::LOG_APPENDER_FILE:
                        $list[][0] = self::LOG_APPENDER_FILE;
                        $list[][1] = "FILE";
                        break;
                    case self::LOG_APPENDER_EMAIL:
                        $list[][0] = self::LOG_APPENDER_EMAIL;
                        $list[][1] = "EMAIL";
                        break;
                    case self::LOG_APPENDER_DB:
                        $list[][0] = self::LOG_APPENDER_DB;
                        $list[][1] = "DB";
                        break;
                    case self::LOG_APPENDER_FIREPHP:
                        $list[][0] = self::LOG_APPENDER_FIREPHP;
                        $list[][1] = "FIREPHP";
                        break;	    			
                }
                
            }
            
            return $list;
            
        }
	   
        /**
         * setta il formato data del log usa la stessa sintassi del comando date di php
         * @param string $date_format
         */
	    public function setDateFormat($date_format){
	
	        $this->date_format = $date_format;
	      
        }
        
        /**
         * Interpolates context values into the message placeholders.
         */
        private function interpolate($message, array $context = array())
        {
            // build a replacement array with braces around the context keys
            $replace = array();
            foreach ($context as $key => $val) {
                // check that the value can be cast to string
                if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                    $replace['{' . $key . '}'] = $val;
                }
            }

            // interpolate replacement values into the message and return
            return strtr($message, $replace);
        }
            
        /**
         * Aggiunge una riga la log
         * @param integer $level livello dell'errore
         * @param string $msg messaggio di errore
         */
        private function append($level, $msg){
                
            $logrow = new ELogRow();
            $logrow->date = date($this->date_format);
            $logrow->message = $msg;
            $logrow->type = $level;
                
            $keys = array_keys($this->appenders);
                
            foreach($keys as $key){
                $this->appenders[$key]->add($logrow);
            }
                            
        }
   
        /**
         * Generic log.
         *
         * @param integer $level: this param can aasume only values in ELogLevel
         * @param string $message
         * @param array  $context
         *
         * @return void
         */
        public function log($level, $message, array $context = array())
        {   
            settype($elevel,"integer");
           
            //PSR-3 Log Levels Mapping
            switch($level){
                case LogLevel::EMERGENCY:
                    $elevel = ELogLevel::EMERGENCY;
                    break;
                case LogLevel::ALERT:
                    $elevel = ELogLevel::ALERT;
                    break;
                case LogLevel::CRITICAL:
                    $elevel = ELogLevel::CRITICAL;
                    break;
                case LogLevel::ERROR:
                    $elevel = ELogLevel::ERROR;
                    break;
                case LogLevel::WARNING:
                    $elevel = ELogLevel::WARNING;
                    break;
                case LogLevel::NOTICE:
                    $elevel = ELogLevel::NOTICE;
                    break;
                case LogLevel::DEBUG:
                    $elevel = ELogLevel::DEBUG;
                    break;
                default:
                    $elevel = $level;
                    break;
            }

            $this->append($elevel, $this->interpolate($message, $context));
        }

        /**
         * System is unusable.
         *
         * @param string $message
         * @param array  $context
         *
         * @return void
         */
        public function emergency($message, array $context = array())
        {
            $this->log(ELogLevel::EMERGENCY, $message, $context);
        }

        /**
         * Action must be taken immediately.
         *
         * Example: Entire website down, database unavailable, etc. This should
         * trigger the SMS alerts and wake you up.
         *
         * @param string $message
         * @param array  $context
         *
         * @return void
         */
        public function alert($message, array $context = array())
        {
            $this->log(ELogLevel::ALERT, $message, $context);
        }

        /**
         * Critical conditions.
         *
         * Example: Application component unavailable, unexpected exception.
         *
         * @param string $message
         * @param array  $context
         *
         * @return void
         */
        public function critical($message, array $context = array())
        {
            $this->log(ELogLevel::CRITICAL, $message, $context);
        }

        /**
         * Runtime errors that do not require immediate action but should typically
         * be logged and monitored.
         *
         * @param string $message
         * @param array  $context
         *
         * @return void
         */
        public function error($message, array $context = array())
        {
            $this->log(ELogLevel::ERROR, $message, $context);
        }

        /**
         * Exceptional occurrences that are not errors.
         *
         * Example: Use of deprecated APIs, poor use of an API, undesirable things
         * that are not necessarily wrong.
         *
         * @param string $message
         * @param array  $context
         *
         * @return void
         */
        public function warning($message, array $context = array())
        {
            $this->log(ELogLevel::WARNING, $message, $context);
        }

        /**
         * Normal but significant events.
         *
         * @param string $message
         * @param array  $context
         *
         * @return void
         */
        public function notice($message, array $context = array())
        {
            $this->log(ELogLevel::NOTICE, $message, $context);
        }

        /**
         * Interesting events.
         *
         * Example: User logs in, SQL logs.
         *
         * @param string $message
         * @param array  $context
         *
         * @return void
         */
        public function info($message, array $context = array())
        {
            $this->log(ELogLevel::INFO, $message, $context);
        }

        /**
         * Detailed debug information.
         *
         * @param string $message
         * @param array  $context
         *
         * @return void
         */
        public function debug($message, array $context = array())
        {
            $this->log(ELogLevel::DEBUG, $message, $context);
        }
    
    }
?> 