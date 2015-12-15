<?php
 
    namespace HZSystem\Core\Logger;
 	
    use HZSystem\Core\Logger\Appenders;
    use HZSystem\Core\Logger\Appenders\Appender_file;
    use HZSystem\Core\Logger\Appenders\Appender_email;
    use HZSystem\Core\Logger\Appenders\Appender_db;
    use HZSystem\Core\Logger\Appenders\Appender_firephp;
	
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
     *  Logger di sistema 
     *
     *  @author  Luca Liscio <hzkight@h0model.org>
     *  @version 0.0.1 2015/11/30 01:44:20
     *  @copyright 2015 Luca Liscio
     *  @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
     *
     *  @package HZSystem
     *  @subpackage Core\Logger
     *  @filesource
     */
	
    class HZLogger {
        
    	// Errors level
    	/** Fatal error - higth level of error */
        const LOG_FATAL =   404;
        /** Normal error - medium level of error */
        const LOG_ERROR =   403;
        /** Warning - low level of error */
        const LOG_WARNING = 402;
        /** Info - info message */
        const LOG_INFO =    401;
        /** Debug - debug message */
        const LOG_DEBUG =   400;

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
    	
    	//Contiene le istanze del logger
    	private static $_instace = array();
            
            /**
	     * Crea una nuova istanza del logger
	     * @param string $logname nome della nuova istanza del logger da creare
	     * @param integer $type tipo di logger da creare 
	     * @return \HZSystem\Core\Logger\HZLogger
	     * @example $miolog = HZLogger::gelLogger("miolog",HZLogger::LOG_APPENDER_FILE);
	     */
	    public static function getLogger($logname,$type=LOG_APPENDER_FILE){
	        
	        if (!(self::$_instace[$logname] instanceof self)){
	            self::$_instace[$logname] = new self($logname,$type);
	        }
	        
	        return self::$_instace[$logname];
	    }
	    
	    /**
	     * Restituisce un array con tutte le istanze del logger
	     * @return HZSystem\Core\Logger\HZLogger
	     */
	    public static function getIstances(){
	    	
	    	return self::$_instace;
	    }
	    
	    /**
	     * Metodo costruttore
	     * @param string $logname nome del logger
	     * @param integer $type tipo di logger da creare
	     */    
	    public function __construct($logname,$type){
	   
	      $this->logname = $logname;
	      $this->add_appender($type);
	      $this->get_appender($type)->setLogLevel(self::LOG_INFO);
	      $this->date_format = "d-m-Y H:m:s";
	      
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
	    		throw new AppenderNotFoundException("Appender richiesto non trovato");
	    	}
	    
	    }
	    
	    /**
	     * restituisce l'appender associato al tipo specifiato
	     * @param integer $type tipo di appender che si vuole ottenere
	     * @throws AppenderNotFoundException
	     * @return HZSystem\Core\Logger\Appenders\Appender
	     */
	    public function get_appender($type){
	    	
	    	if(isset($this->appenders[$type])){
	    		return $this->appenders[$type];
	    	} else {
	    		throw new AppenderNotFoundException("Appender richiesto non trovato");
	    	}
	    	
	    }
	   
		/**
		 * setta il formato data del log usa la stessa sintassi del comando date di php
		 * @param string $date_format
		 */
	    public function setDateFormat($date_format){
	   
	    	$this->date_format = $date_format;
	      
	    }
   
    public function append($level, $msg){
     
      if ($level<=$this->loglevel)
        error_log(date($this->date_format).' --> '.$msg."\n",3,$this->logfile);
    }
   
    public function error($msg){
      if ($this->loglevel>=constant("LOG_ERROR"))
        error_log(date($this->date_format).' --> '.$msg."\n",3,$this->logfile);
    }
   
    public function warning($msg){
      if ($this->loglevel>=constant("LOG_WARNING"))
        error_log(date($this->date_format).' --> '.$msg."\n",3,$this->logfile);
    }

    public function info($msg){
      if ($this->loglevel>=constant("LOG_INFO"))
        error_log(date($this->date_format).' --> '.$msg."\n",3,$this->logfile);
    }
   
    public function debug($msg){
      if ($this->loglevel>=constant("LOG_DEBUG"))
        error_log(date($this->date_format).' --> '.$msg."\n",$this->log_type,$this->logfile);
    }
   
   
   
  }
?> 