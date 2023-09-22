<?php
    /* 
     * httprequest.class.php
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
     * Copyright (C)2023 HZKnight
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

	/**
     * Classe che rappresenta la richiesta http
     * 
     * @author  lucliscio <lucliscio@h0model.org>
     * @version v 2.0 2023/07/08 09:32:20
     * @copyright Copyright 2023 HZKnight
     * @copyright Copyright 2013 Luca Liscio & Marco Lettieri 
     * @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
     *   
     * @package eXperience/Cms
     * @filesource
     */

     namespace Experience\Core\Utility;

	class HttpRequest {
		private $_requestParams = array();
          private $_requestMethod = "";
		
          /**
           * Costruttore
           */
		public function __construct() {
			$this->_requestParams['get'] = $_GET;
               $this->_requestParams['post'] = $_POST;
               $this->_requestParams['cookie'] = $_COOKIE;

               $this->_requestMethod = strtolower($_SERVER['REQUEST_METHOD']);
		}
		
          /**
           * Restituisce un parametro della richiesta
           * nel caso il parametro non esista restituisce la
           * stringa vuota
           *
           * @param string $paramName
           * @return mixed
           */
		public function getParam($paramName) {

               //Do priorita all'array che rappresenta il metodo della richiesta
               if (in_array($paramName, array_keys($this->_requestParams[$this->_requestMethod]))) {
                    return $this->_requestParams[$this->_requestMethod][$paramName];
               }

               //Cerco in tutti gli altri array
               foreach ($this->_requestParams as $key => $value) {
                    if($key != $this->_requestMethod){
                         if (in_array($paramName, array_keys($value))) {
                              return $value[$paramName];
                         }
                    }
               }

			return '';

		}
		
          /**
           * Restituisce per ogni parametro l metodo dells richiesta
           * con cui sono arrivati
           *
           * @param string $paramName
           * @return string
           */
          public function getParamRequestMethod($paramName) {
               
               //Do priorita all'array che rappresenta il metodo della richiesta
               if (in_array($paramName, array_keys($this->_requestParams[$this->_requestMethod]))) {
                    return $this->_requestMethod;
               }

               //Cerco in tutti gli altri array
               foreach ($this->_requestParams as $key => $value) {
                    if (in_array($paramName, array_keys($value))) {
                         return $key;
                    }
               }
			return '';
		}

          /**
           * Verifica se un parametro è dentro la request
           *
           * @param string $paramName
           * @return boolean
           */
		public function has($paramName){
               $exist = false;
               foreach ($this->_requestParams as &$value) {
                    if (in_array($paramName, array_keys($value))) {
                         $exist = true;
                    }
               }
			return $exist;
		}

          /**
           * Inserisce n parametro alla request
           *
           * @param string $paramName
           * @param string $rtype
           * @param mixed $value
           * @return void
           */
		public function setParam($paramName, $rtype, $value){
			$this->_requestParams[$rtype][$paramName] = $value;
		}
		
          /**
           * Undocumented function
           *
           * @return void
           */
		public function getRequest() {
			return $this->_requestParams;
		}

          public function getRequestMethod() {
			return $this->_requestMethod;
		}
	}