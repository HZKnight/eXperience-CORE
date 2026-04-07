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

     namespace Experience\Core\Io\Net\Http;

     use function array_keys;
     use function in_array;
     use function strtolower;


    /**
     * Classe che rappresenta la richiesta http
     *
     * @author  lucliscio <lucliscio@h0model.org>
     * @version 2.3.0
     * @copyright &copy;2023-2026 HZKnight
     * @copyright &copy;2013 Luca Liscio & Marco Lettieri
     * @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
     *
     * @package eXperience
     * @subpackage Core\Io\Net\Http
     *
     * @filesource
     */

     class HttpRequest {
          private $requestParams = [];
          private $requestMethod = "";

          /**
           * Costruttore
           */
          public function __construct() {
               $this->requestParams['get'] = $_GET;
               $this->requestParams['post'] = $_POST;
               $this->requestParams['cookie'] = $_COOKIE;

               $this->requestMethod = strtolower($_SERVER['REQUEST_METHOD']);
          }

          /**
           * Restituisce un parametro della richiesta
           * nel caso il parametro non esista restituisce la
           * stringa vuota
           *
           * @param string $paramName
           * @return mixed
           */
          public function getParam(string $paramName): mixed {

               //Do priorita all'array che rappresenta il metodo della richiesta
               if (in_array($paramName, array_keys($this->requestParams[$this->requestMethod]))) {
                    return $this->requestParams[$this->requestMethod][$paramName];
               }

               //Cerco in tutti gli altri array
               foreach ($this->requestParams as $key => $value) {
                    if(($key != $this->requestMethod) && in_array($paramName, array_keys($value))) {
                         return $value[$paramName];
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
          public function getParamRequestMethod(string $paramName): string {
               
               //Do priorita all'array che rappresenta il metodo della richiesta
               if (in_array($paramName, array_keys($this->requestParams[$this->requestMethod]))) {
                    return $this->requestMethod;
               }

               //Cerco in tutti gli altri array
               foreach ($this->requestParams as $key => $value) {
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
          public function has(string $paramName){
               $exist = false;
               foreach ($this->requestParams as &$value) {
                    if (in_array($paramName, array_keys($value))) {
                         $exist = true;
                    }
               }
               return $exist;
          }

          /**
           * Setta un parametro della richiesta
           *
           * @param string $paramName
           * @param string $rtype
           * @param mixed $value
           * @return void
           */
          public function setParam(string $paramName, string $rtype, mixed $value){
               $this->requestParams[$rtype][$paramName] = $value;
          }

          /**
           * Restituisce tutti i parametri della richiesta
           *
           * @return array
           */
          public function getRequest(): array {
               return $this->requestParams;
          }

          /**
           * Restituisce il metodo della richiesta
           *
           * @return string
           */
          public function getRequestMethod(): string {
               return $this->requestMethod;
          }
     }
