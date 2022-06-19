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
     * Copyright (C) 2022 HZKnight
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


    namespace Experience\Exceptions;

	/**
     * Interfaccia generica per le eccezioni basata sulla interfaccia prevista dal linguaggio PHP
     *
     *  @author  Luca Liscio <lucliscio@h0model.org>
     *  @version 0.0.1 2016/05/31 12:14:20
     *  @copyright 2022 HZKnight
     *  @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
     *
     *  @package Experience
     *  @subpackage exceptions
     *  @filesource
     */
    interface IException
    {
        /* Protected methods inherited from Exception class */
        public function getMessage();                 // Exception message
        public function getCode();                    // User-defined Exception code
        public function getFile();                    // Source filename
        public function getLine();                    // Source line
        public function getTrace();                   // An array of the backtrace()
        public function getTraceAsString();           // Formated string of trace
	
        /* Overrideable methods inherited from Exception class */
        public function __toString();                 // formated string for display
        public function __construct($message = null, $code = 0);
    }
	

    /**
     * Eccezione generica per Experience
     *
     *  @author  Luca Liscio <lucliscio@h0model.org>
     *  @version 0.0.2 2020/11/29 20:14:20
     *  @copyright 2021 HZKnight
     *  @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
     *
     *  @package Experience
     *  @subpackage exceptions
     *  @filesource
     */
    abstract class EException extends Exception implements IException
    {
        protected $message = "";                      // Exception message
        private   $string;                            // Unknown
        protected $code    = 0;                       // User-defined exception code
        protected $file;                              // Source filename of exception
        protected $line;                              // Source line of exception
        private   $trace;                             // Unknown
        
        /**
         * Constructor
         * 
         * @param String $message Error Message
         * @param String $code Error Code
         */
        public function __construct($message = null, $code = 0)
	    {
            if (!$message) {
                $this->message = dgettext("ELang",'Unknown exception');
                throw new $this($this->message. get_class($this));
            }
            parent::__construct($message, $code);
	    }
	
        /**
         * To String Method 
         * 
         * @return String
         */
        public function __toString()
        {
            return get_class($this) . " '{$this->message}' in {$this->file}({$this->line})\n" . "{$this->getTraceAsString()}";
	    }
        
        /**
         * Return the error code
         * 
         * @return String
         */
        public function getCode() {
            return parent::getCode();
        }
        
        /**
         * It returns the file where the exception occurred
         * 
         * @return String
         */
        public function getFile() {
            return parent::getFile();
        }

        /**
         * It returns the line in file where the exception occurred
         * 
         * @return integer
         */
        public function getLine() {
            return parent::getLine();
        }

        /**
         * It returns the exception message
         * 
         * @return type
         */
        public function getMessage() {
            return parent::getMessage();
        }

        /**
         * It returns the exception trace
         * 
         * @return array
         */
        public function getTrace() {
            return parent::getTrace();
        }

        /**
         * It returns the exception trace
         * 
         * @return string
         */
        public function getTraceAsString() {
            return parent::getTraceAsString();
        }

    }
?>