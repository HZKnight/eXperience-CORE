<?php
   
    namespace Experiance\Core\Config;
    
    use Experience\Core\Config\Exceptions;

   /*
    * Copyright (C) 2020 HZKnight
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
    *  Gestore della configurazione. Il file di configurazione deve essere di tipo JSon
    * 
    *  @author Luca Liscio <hzkight@h0model.org>
    *  @author Marco Lettieri
    *  @version v 1.1 2016/09/26 10:56:20
    *  @copyright &copy;2020 HZKnight
    *  @copyright &copy;2013 Luca Liscio & Marco Lettieri 
    *  @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
    *   
    *  @package hzSystem
    *  @subpackage Core
    *  @filesource
    */
    class HZConfig{
        private $cfg;
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
                throw new ConfigException(dgettext("hzSystem","Config file not exist"),103);
            } else if (($this->cfg=json_decode(file_get_contents($cfile), true))==null){
                throw new ConfigException(dgettext("hzSystem","Config file is corrupted"),113); 
            }
             
            $this->cfgfile = $cfile;
        }
          
        /**
         * Restituisce l'intera configurazione
         * 
         * @return array 
         */
        public function get_cfg(){
            return $this->cfg;
        }
         
        /**
         * Restituisce il contenuto di una voce della configuarazione
         * 
         * @param string $param nome del parametro
         * @return mixed
         */
        public function get_param($param){
            return $this->cfg[$param];
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
            if (array_key_exists($param, $this->cfg)){
                $this->cfg[$param] = $val;
                $this->save_cfg();
            }
        }
         
        private function set_param3($section,$param,$val){
            if (array_key_exists($section, $this->cfg)){
                if(array_key_exists($param, $this->cfg[$section])){
                    $this->cfg[$section][$param] = $val;
                }
                $this->save_cfg();
            }
        }
                  
        private function save_cfg(){
            $status = file_put_contents($this->cfgfile, json_encode($this->cfg));
            if(!$status)throw new ConfigException(dgettext("hzSystem","Config file isn't wirittable"),123);
        }
    }
?>