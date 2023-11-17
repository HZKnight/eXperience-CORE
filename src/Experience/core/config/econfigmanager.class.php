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
    * Copyright (C) 2021 HZKnight
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
   
    namespace Experience\Core\Config;
    
    use Experience\Core\Config\Exceptions\ConfigException;

   /**
    *  Gestore della configurazione. Il file di configurazione deve essere di tipo JSon
    * 
    *  @author Luca Liscio <lucliscio@h0model.org>
    *  @author Marco Lettieri
    *  @version v 1.2 2020/11/29 20:56:20
    *  @copyright &copy;2021 HZKnight
    *  @copyright &copy;2013 Luca Liscio & Marco Lettieri 
    *  @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
    *   
    *  @package Experience
    *  @subpackage Core\config
    *  @filesource
    */
    class EConfigManager{
        private array $cfg;
        private $cfgJson;
        private $cfgfile;
         
        /**
         * Costruttore della classe Config, prende in input il path del file di configurazione
         * e ne carica il contenuto. Il file deve essere in formato json.
         * 
         * @param string $cfile path del file di configurazione
         * @throws ConfigException
         */
        public function __construct($cfile){
            $json;
             
            if (!file_exists($cfile)){
                throw new ConfigException(dgettext("ELang","Config file not exist"),103);
            } else if (($this->cfgJson=json_decode(file_get_contents($cfile), true))==null){
                throw new ConfigException(dgettext("Elang","Config file is corrupted"),113); 
            }
             
            $this->cfgfile = $cfile;
            $this->parseCfg();
        }
          
        /**
         * Restituisce l'intera configurazione
         * 
         * @return array 
         */
        public function get_cfg(){
            return $this->cfgJson;
        }
        
        /**
         * Verifica l'esistenza di un parametro nella configurazione
         *
         * @param [type] $param
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
        public function get_param($param){
            if($this->has($param)){
                return $this->cfg[$param];
            } else {
                return null;
            }
        }
         
        /**
         * Aggiorna il valore di una voce della configurazione
         * 
         * 
         */
        public function set_param(){
            $numArgs = func_num_args() ; 
            $args = func_get_args() ; 
            call_user_func_array( array(&$this, 'set_param'.$numArgs), $args ) ; 
        }
         
        private function set_param2($param,$val){
            $ex = explode(".", $param);
            if(count($ex) == 1){
                if (array_key_exists($param, $this->cfg)){
                    $this->cfgJson[$param] = $val;
                    $this->cfg[$param] = $val;
                    $this->save_cfg();
                }
            } else {
                $this->set_param3($ex[0], $ex[1], $val);
            }
        }
         
        private function set_param3($section,$param,$val){
            if (!array_key_exists($section, $this->cfgJson)){
                $this->cfgJson[$section] = array();
            }
            $this->cfgJson[$section][$param] = $val;
            $this->cfg[$section.".".$param] = $val;
            
            $this->save_cfg();
        }
                  
        private function save_cfg(){
            $status = file_put_contents($this->cfgfile, json_encode($this->cfgJson, JSON_PRETTY_PRINT));
            if(!$status)throw new ConfigException(dgettext("Elang","Config file isn't wirittable"),123);
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
?>