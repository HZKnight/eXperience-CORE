<?php
    /*
     * eexception.class.php
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


    namespace Experience\Core\Exceptions;

    /**
     * Interfaccia generica per le eccezioni basata sulla interfaccia
     * prevista dal linguaggio PHP
     *
     * @author Luca Liscio <lucliscio@h0model.org>
     * @version 0.0.1
     * @copyright &copy;2016-2026 HZKnight
     * @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
     *
     * @package eXperience
     * @subpackage Core\Exceptions
     *
     * @filesource
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
     * @author  Luca Liscio <lucliscio@h0model.org>
     * @version 0.0.4
     * @copyright &copy;2020-2026 HZKnight
     * @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
     *
     * @package eXperience
     * @subpackage Core\Exceptions
     *
     * @filesource
     */
    class EException extends \Exception implements IException
    {
        protected $message = "";                      // Exception message
        protected $code    = 0;                       // User-defined exception code
        protected string $file;                       // Source filename of exception
        protected int $line;                          // Source line of exception
        private  $internalCode = "E000";              // Internal code for exception
        private  $name = "EException";                // Name of the exception
        
        /**
         * Constructor
         *
         * @param string $message Error Message
         * @param string $code Error Code
         */
        public function __construct($name = "EException", $message = null, $code = 0, $internalCode = "E000")
        {
            // if message in null use message
            if (!$message) {
                $message = dgettext("ELang",'Unknown exception');
            }

            // if $code == 0 use exception default code
            if($code == 0){
                $code = $this->code;
            }

            $this->name = $name;
            $this->internalCode = $internalCode;
            $this->message = $message;
            $this->code = $code;

            parent::__construct($message, $code);
        }

        /**
         * Set variables in error message
         *
         * @param array $vars
         */
        public function prepare(array $vars){
            $this->message = strtr($this->message, $vars);
        }

        /**
         * Set internal code
         *
         * @param string $code
         */
        public function setInternalCode($code){
            $this->internalCode = $code;
        }

        /**
         * Return internal code
         *
         * @return string
         */
        public function getInternalCode(){
            return $this->internalCode;
        }

        public function getName(){
            return $this->name;
        }

        public function setName($name){
            $this->name = $name;
        }

        /**
         * To String Method
         *
         * @return string
         */
        public function __toString(): string
        {
            return get_class($this) . " '{$this->message}' in {$this->file}({$this->line})\n" . "{$this->getTraceAsString()}";
        }

    }
