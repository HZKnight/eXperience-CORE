<?php
    
    /*
     * Copyright (C) 2016 Luca Liscio
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

	
    namespace HZSystem\Exceptions;

    /**
     * Interfaccia generica per le eccezioni basata sulla interfaccia
     * prevista dal linguaggio PHP
     *
     *  @author  Luca Liscio <hzkight@h0model.org>
     *  @version 0.0.1 2016/05/31 12:14:20
     *  @copyright 2016 Luca Liscio
     *  @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
     *
     *  @package HZSystem
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
     * Eccezione generica per HZSystem
     *
     *  @author  Luca Liscio <hzkight@h0model.org>
     *  @version 0.0.1 2016/05/31 12:14:20
     *  @copyright 2016 Luca Liscio
     *  @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
     *
     *  @package HZSystem
     *  @subpackage exceptions
     *  @filesource
     */
    abstract class HZException extends Exception implements IException
    {
	protected $message = "";                      // Exception message
	private   $string;                            // Unknown
	protected $code    = 0;                       // User-defined exception code
	protected $file;                              // Source filename of exception
	protected $line;                              // Source line of exception
	private   $trace;                             // Unknown
	
	public function __construct($message = null, $code = 0)
	{
            if (!$message) {
                $this->message = dgettext("hzSystem",'Unknown exception ');
                throw new $this($this->message. get_class($this));
            }
            parent::__construct($message, $code);
	}
	
	public function __toString()
	{
            return get_class($this) . " '{$this->message}' in {$this->file}({$this->line})\n" . "{$this->getTraceAsString()}";
	}

        public function getCode() {
            return parent::getCode();
        }

        public function getFile() {
            return parent::getFile();
        }

        public function getLine() {
            return parent::getLine();
        }

        public function getMessage() {
            return parent::getMessage();
        }

        public function getTrace() {
            return parent::getTrace();
        }

        public function getTraceAsString() {
            return parent::getTraceAsString();
        }

    }
?>