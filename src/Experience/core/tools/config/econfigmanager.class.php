<?php
    /*
     * econfigmanager.class.php
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
   
    namespace Experience\Core\Tools\Config;
    
    use Experience\Core\Tools\Config\Exceptions\ConfigException;
    use Experience\Core\Io\Storage\Estorage;


   /**
    * Gestore della configurazione. Il file di configurazione deve essere di tipo JSon
    *
    * @author Luca Liscio <lucliscio@h0model.org>
    * @author Marco Lettieri
    * @version 2.1.0
    * @copyright &copy;2025 HZKnight
    * @copyright &copy;2013 Luca Liscio & Marco Lettieri
    * @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
    *
    * @package eXperience
    * @subpackage Core\Tools\Config
    *
    * @filesource
    */

    class EConfigManager{
        private array $cfg;
        private $cfgJson;
        private $cfgfile;
        private EStorage $storage;
         
        /**
         * Costruttore della classe Config, prende in input il path del file di configurazione
         * e ne carica il contenuto. Il file deve essere in formato json.
         *
         * @param string $cfile path del file di configurazione
         * @param EStorage $storage oggetto storage
         * @throws ConfigException
         */
        public function __construct($cfile, $storage=null){
            if($storage){
                $this->storage = $storage;

                if (!$this->storage->fileExists($cfile)){
                    throw new ConfigException(dgettext("ELang","Config file not exist"),103);
                } elseif (($this->cfgJson=json_decode($this->storage->fileRead($cfile), true))==null){
                    throw new ConfigException(dgettext("Elang","Config file is corrupted"),113);
                }
                 
                $this->cfgfile = $cfile;
                $this->parseCfg();

            } else {
                throw new ConfigException("Invalid Storage",103);
            }
        }
          
        /**
         * Restituisce l'intera configurazione
         *
         * @return array
         */
        public function getCfg(){
            return $this->cfgJson;
        }
        
        /**
         * Verifica l'esistenza di un parametro nella configurazione
         *
         * @param string $param
         * @return boolean
         */
        public function has($param){
            return in_array($param, array_keys($this->cfg));
        }

        /**
         * Restituisce il contenuto di una voce della configuarazione
         *
         * @param string $param nome del parametro
         * @return mixed
         */
        public function getParam($param){
            if($this->has($param)){
                return $this->cfg[$param];
            } else {
                return null;
            }
        }
         
        /**
         * Aggiorna il valore di una voce della configurazione
         */
        public function setParam(){
            $numArgs = func_num_args();
            $args = func_get_args();
            call_user_func_array( array(&$this, 'setParam'.$numArgs), $args );
        }
         
        private function setParam2($param,$val){
            $ex = explode(".", $param);
            if(count($ex) == 1){
                if (array_key_exists($param, $this->cfg)){
                    $this->cfgJson[$param] = $val;
                    $this->cfg[$param] = $val;
                    $this->saveCfg();
                }
            } else {
                $this->setParam3($ex[0], $ex[1], $val);
            }
        }
         
        private function setParam3($section,$param,$val){
            if (!array_key_exists($section, $this->cfgJson)){
                $this->cfgJson[$section] = array();
            }
            $this->cfgJson[$section][$param] = $val;
            $this->cfg[$section.".".$param] = $val;
            
            $this->saveCfg();
        }
                  
        private function saveCfg(){
            $status = $this->storage->fileWrite($this->cfgfile, json_encode($this->cfgJson, JSON_PRETTY_PRINT), "w");
            if(!$status) {
                throw new ConfigException(dgettext("Elang","Config file isn't wirittable"),123);
            }
        }

        private function parseCfg(){
            $this->cfg = array();
            $keys = array_keys($this->cfgJson);
            foreach ($keys as &$key) {
                $value = $this->cfgJson[$key];
                if(is_array($value)){
                    $subKeys = array_keys($value);
                    foreach ($subKeys as &$subkey) {
                        $compKey = $key.".".$subkey;
                        $this->cfg[$compKey] = $value[$subkey];
                    }
                } else {
                    $this->cfg[$key] = $value;
                }
            }
        }

    }
