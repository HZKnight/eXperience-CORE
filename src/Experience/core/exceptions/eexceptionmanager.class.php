<?php
    /*
     * eexceptionmaneger.class.php
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
    
    use Experience\Core\Exceptions\EException;
    
    use function array_keys;

    
    /**
     * Questa classe permette di definire e lanciare eccesioni personalizzate
     *
     * @author  Luca Liscio <lucliscio@h0model.org>
     * @version 2.0.0
     * @copyright @copy;2026 HZKnight
     * @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
     *
     * @package eXperience
     * @subpackage Core\Exceptions
     *
     * @filesource
     */

    class EExceptionManager {

        private static $instance;
        private static array $registry = [];

        /**
         * Return instace of EExceptionManager
         *
         * @return EExceptionManager
         */
        public static function getExceptionManager(): EExceptionManager {
            if(!self::$instance){
                self::$instance = new self();
            }
            return self::$instance;
        }

        /**
         * Costruttore
         */
        private function __construct(){
            self::$registry = array();
            self::addException("ENotApplicableMethodException", dgettext("Elang","Not applicable method exception"), "EE000");
        }

        /**
         * Add new excepion
         *
         * @param string $name
         * @param string $message
         * @param string $code
         * @return void
         */
        public static function addException(string $name, string $message, string $code){

            // Creiamo un alias della nostra classe base con il nuovo nome
            if (!class_exists($name)) {
                class_alias(EException::class, $name);

                $hash = sprintf("%u", crc32($code));
                $ncode = str_pad($hash % 1000000, 6, '0', STR_PAD_LEFT);
            
                self::$registry[$name] = array(
                    "name" => $name,
                    "message" => $message,
                    "code" => $ncode,
                    "internalCode" => $code
                );
            }

        }

        /**
         * Return exceptions list
         *
         * @return array
         */
        public function getExceptionList(): array {
            return array_keys(self::$registry);
        }

        /**
         * Throw exception
         *
         * @param string $name
         * @param array $vars
         * @return void
         */
        public static function throwException(string $name, ?array $vars = null){
            if(!array_key_exists($name, self::$registry)){
                $name = "ENotApplicableMethodException";
            }

            $ex = new $name(self::$registry[$name]['name'], self::$registry[$name]['message'], self::$registry[$name]['code'], self::$registry[$name]['internalCode']);

            if($vars !== null){
                $ex->prepare($vars);
            }
            throw $ex;
        }

    }
