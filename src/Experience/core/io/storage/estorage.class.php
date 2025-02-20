<?php

    /*
     * estorage.class.php
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

    namespace Experience\Core\Io\Storage;
    
    use Experience\Core\Io\Storage\Driver\Interface\StorageDriveInterface;
    use Experience\Core\Io\Storage\Exceptions\StorageException;

    /**
     * Storage interface class
     *
     * @author lucliscio <lucliscio@h0model.org>
     * @version 1.1.1
     * @copyright &copy;2025 HZKnight
     * @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
     *
     * @package eXperience
     * @subpackage Core\Io\Storage
     *
     * @filesource
     */

     class EStorage {

        //Dati del logger
        private $storagename;
        private StorageDriveInterface $driver;
        private string $webRoot;

        //Contiene le istanze di Estoreg
        private static $instace = array();
        
        /**
         * Crea una nuova istanza dello storage
         *
         * @param string $soragename nome della nuova istanza dello storage da creare
         * @param integer $driver una implementazione dello storage driver
         * @return EStorage
         * @example $mioStorage = EStorage::getStorage("miostorage",$driver);
         */
        public static function getStorage($storagename, StorageDriveInterface $driver, $connString="/"): EStorage {
            if(array_key_exists($storagename, self::$instace)) {
                if (!(self::$instace[$storagename] instanceof self)){
                    self::$instace[$storagename] = new self($storagename,$driver,$connString);
                }
            } else {
                self::$instace[$storagename] = new self($storagename,$driver,$connString);
            }
            
            return self::$instace[$storagename];
        }
            
        /**
         * Restituisce un array con tutte le istanze dello storage
         *
         * @return EStorage[]
         */
        public static function getIstances(){
                
            return self::$instace;

        }
        
        /**
         * Metodo costruttore
         *
         * @param string $storagename nome dello storage da creare
         * @param integer $driver una implementazione dello storage driver
         * @param string $connString Stringa di connessione allo storage
         */
        private function __construct($storagename, StorageDriveInterface $driver, $connString){
            $this->storagename = $storagename;
            $this->driver = $driver;
            if($this->driver->connectToStorage($connString)){
                $this->webRoot = $driver->getWebRoot();
            } else {
                throw new StorageException("Connection to storage failed");
            }
        }

        public function mkdir($name, $mode=0777){
            return $this->driver->mkdir($name, $mode);
        }

        public function rm($name):bool{
            return $this->driver->rm($name);
        }

        public function fcopy($source,$target){
            return $this->driver->fcopy($source,$target);
        }

        public function ls($dir="./",$pattern="*.*"):array{
            return $this->driver->ls($dir,$pattern);
        }

        public function fileCompare($src, $dest):bool{
            return $this->driver->fileCompare($src, $dest);
        }

        public function fileExists($src):bool{
            return $this->driver->fileExists($src);
        }

        public function fileWrite($name,$content,$mode="a"):bool{
            return $this->driver->fileWrite($name,$content,$mode);
        }

        public function fileRead($name):mixed{
            return $this->driver->fileRead($name);
        }

        public function isDir($name):bool{
            return $this->driver->isDir($name);
        }

        /**
         * Create new file in storage
         *
         * @param string $name
         * @param mixed $content
         * @return boolean
         */
        public function fileCreate($name,$content): bool{
            settype($name,"string");
            
            $source = $this->webRoot.$name;

            if($this->fileExists($source)){
                throw new StorageException("System Error: fileCreate(".$source.",...). File already exist");
            } else {

                if($this->fileWrite($name,$content,"wb") && $this->fileExists($source)){
                    return true;
                }

                throw new StorageException("System Error: fileCreate(".$source.",...).");
            }

        }

     }
