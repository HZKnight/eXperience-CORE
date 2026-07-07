<?php
    /*
     * elogger.class.php
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
     * License
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
    
    namespace Experience\Core\Tools\Logger;
    
    use Experience\Core\Exceptions\EExceptionManager;
    use Experience\Core\Exceptions\EException;

    use Experience\Core\Tools\Logger\Appenders\Appender;
    use Experience\Core\Tools\Logger\Appenders\AppenderFile;
    use Experience\Core\Tools\Logger\Appenders\AppenderEmail;
    use Experience\Core\Tools\Logger\Appenders\AppenderDb;
    use Experience\Core\Tools\Config\EConfigManager;
    use Experience\Core\Tools\Logger\ELogLevel;
    use Experience\Core\Tools\Logger\ELogRow;
    use Experience\Core\Io\Storage\EStorage;
    use Psr\Log\LoggerInterface;
    use Psr\Log\LogLevel;

    use function array_key_exists;
    use function settype;
    use function array_keys;
    use function strtr;
    use function date;

    /**
     * Logger di sistema
     *
     * @author  Luca Liscio <lucliscio@h0model.org>
     * @version 2.1.0
     * @copyright &copy;2021-2026 HZKnight
     * @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
     *
     * @package eXperience
     * @subpackage Core\Tools\Logger
     *
     * @filesource
     */

    class ELogger implements LoggerInterface {
        
        // Appenders type
        /** File type log */
        public const LOG_APPENDER_FILE =    501;
        /** Email type log */
        public const LOG_APPENDER_EMAIL =   502;
        /** Data base type log */
        public const LOG_APPENDER_DB =      503;

        //Dati del logger
        private $logname;
        private $dateFormat;

        /** @var Appender[] */
        private array $appenders = [];

        private $loglevel;
        private EConfigManager $cfg;
        private EStorage $storage;

        //Contiene le istanze del logger
        private static $instace = array();

        /**
         * Crea una nuova istanza del logger
         *
         * @param EConfigManager $cfg oggetto di configurazione
         * @param EStorage $storage oggetto storage
         * @param string $logname nome della nuova istanza del logger da creare
         * @param integer $type tipo di logger da creare
         * @param integer $loglevel livello di errore da cui cominciare a registrare il log
         * @return ELogger
         *
         * @example $miolog = ELogger::getLogger($cfg, $storage, "miolog",ELogger::LOG_APPENDER_FILE,ELogLevel::INFO);
         *
         */
        public static function getLogger(EConfigManager $cfg, EStorage $storage, string $logname,$type=self::LOG_APPENDER_FILE, $loglevel=ELogLevel::INFO){
            if(array_key_exists($logname, self::$instace)) {
                if (!(self::$instace[$logname] instanceof self)){
                    self::$instace[$logname] = new self($logname,$type,$loglevel,$cfg,$storage);
                }
            } else {
                self::$instace[$logname] = new self($logname,$type,$loglevel,$cfg,$storage);
            }
            
            return self::$instace[$logname];
        }

        /**
         * Restituisce un array con tutte le istanze del logger
         * @return ELogger[]
         */
        public static function getIstances(){
            return self::$instace;
        }

        /**
         * Metodo costruttore
         *
         * @param string $logname nome del logger
         * @param integer $type tipo di logger da creare
         * @param integer $loglevel livello di errore da cui cominciare a registrare il log
         */
        private function __construct($logname, $type, $loglevel, EConfigManager $cfg, EStorage $storage){

            $this->cfg = $cfg;
            $this->logname = $logname;
            $this->loglevel = $loglevel;
            $this->storage = $storage;
            $this->add_appender($type);
            $this->get_appender($type)->setLogLevel($this->loglevel);
            $this->dateFormat = "d-m-Y H:i:s";

            EExceptionManager::addException("AppenderNotFoundException", dgettext("Elang","Appender requested not found"), "ELE001");

        }

        /**
         * Aggiunge un nuovo appender al logger
         *
         * @param integer $type tipo di appender da aggiungere
         */
        public function add_appender(int $type){

            switch($type){
                case self::LOG_APPENDER_FILE:
                    $this->appenders[$type] = new AppenderFile($this->logname, $this->cfg, $this->storage);
                    break;
                case self::LOG_APPENDER_EMAIL:
                    $this->appenders[$type] = new AppenderEmail($this->logname, $this->cfg);
                    break;
                case self::LOG_APPENDER_DB:
                    $this->appenders[$type] = new AppenderDb($this->logname, $this->cfg);
                    break;
                default:
                    $this->appenders[$type] = new AppenderFile($this->logname, $this->cfg, $this->storage);
                    break;
            }
                
            $this->get_appender($type)->setLogLevel($this->loglevel);

        }

        /**
         * Rimuove un appender dal logger
         *
         * @param integer $type tipo di appender da rimuovere
         * @throws EException
         */
        public function remove_appender(int $type){

            if(isset($this->appenders[$type])){
                unset($this->appenders[$type]);
            } else {
                EExceptionManager::throwException("AppenderNotFoundException");
            }
            
        }

        /**
         * restituisce l'appender associato al tipo specifiato
         *
         * @param integer $type tipo di appender che si vuole ottenere
         * @throws EException
         * @return Appenders\Appender|null
         */
        public function get_appender(int $type){

            if(isset($this->appenders[$type])){
                return $this->appenders[$type];
            } else {
                EExceptionManager::throwException("AppenderNotFoundException");
                return null;
            }

        }
        
        /**
         * Restituisce la lista degli appenders attivi sotto forma di array
         * bidimensionale [codice][tipo]
         *
         * @return array
         */
        public function get_appenders_list(){
            
            $list = [];
            
            $keys = array_keys($this->appenders);
            
            foreach($keys as $key){
                
                switch($key){
                    case self::LOG_APPENDER_FILE:
                        $list[] = array(self::LOG_APPENDER_FILE, "FILE");
                        break;
                    case self::LOG_APPENDER_EMAIL:
                        $list[] = array(self::LOG_APPENDER_EMAIL, "EMAIL");
                        break;
                    case self::LOG_APPENDER_DB:
                        $list[] = array(self::LOG_APPENDER_DB, "DB");
                        break;
                    default:
                        break;
                }
                
            }
            
            return $list;
            
        }

        /**
         * setta il formato data del log usa la stessa sintassi del comando date di php
         *
         * @param string $dateFormat
         */
        public function setDateFormat(string $dateFormat){
            $this->dateFormat = $dateFormat;
        }

        /**
         * Interpolates context values into the message placeholders.
         *
         * @param string $message
         * @param array  $context
         *
         * @return string
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
        private function append(int $level, string $msg){

            $logrow = new ELogRow();
            $logrow->date = date($this->dateFormat);
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
         * @param mixed $level: this param can aasume only values in ELogLevel
         * @param string $message
         * @param array  $context
         *
         * @return void
         */
        public function log($level, $message, array $context = [])
        {
            settype($elevel,"integer");

            echo "Logging message: $message with level: $level\n"; // Debug output
            // Assicurati che gestisca le stringhe minuscole di PSR-3 mappandole sulle tue costanti intere ELogLevel
            switch($level){
                case 'emergency':
                case LogLevel::EMERGENCY:
                    $elevel = ELogLevel::EMERGENCY;
                    break;
                case 'alert':
                case LogLevel::ALERT:
                    $elevel = ELogLevel::ALERT;
                    break;
                case 'critical':
                case LogLevel::CRITICAL:
                    $elevel = ELogLevel::CRITICAL;
                    break;
                case 'error':
                case LogLevel::ERROR:
                    $elevel = ELogLevel::ERROR;
                    break;
                case 'warning':
                case LogLevel::WARNING:
                    $elevel = ELogLevel::WARNING;
                    break;
                case 'notice':
                case LogLevel::NOTICE:
                    $elevel = ELogLevel::NOTICE;
                    break;
                case 'info':
                case LogLevel::INFO:
                    $elevel = ELogLevel::INFO;
                    break;
                case 'debug':
                case LogLevel::DEBUG:
                    $elevel = ELogLevel::DEBUG;
                    break;
                default:
                    $elevel = (int)$level;
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
