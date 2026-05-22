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

    namespace Experience\Core\Io\Storage;
    
    use Experience\Core\Exceptions\EExceptionManager;
    use Experience\Core\Io\Storage\Driver\StorageDriver;

    use function array_key_exists;
    use function settype;

    /**
     * Storage interface class
     *
     * @author lucliscio <lucliscio@h0model.org>
     * @version 1.1.4
     * @copyright &copy;2026 HZKnight
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
        private StorageDriver $driver;
        private string $webRoot;

        //Contiene le istanze di Estoreg
        private static $instace = array();
        
        /**
         * Crea una nuova istanza dello storage
         *
         * @param string $storagename nome della nuova istanza dello storage da creare
         * @param StorageDriver $driver una implementazione dello storage driver
         * @return EStorage
         * @example $mioStorage = EStorage::getStorage("miostorage",$driver);
         */
        public static function getStorage($storagename, StorageDriver $driver, string $connString="/"): EStorage {
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
        public static function getInstances(): array{
                
            return self::$instace;

        }
        
        /**
         * Metodo costruttore
         *
         * @param string $storagename nome dello storage da creare
         * @param StorageDriver $driver una implementazione dello storage driver
         * @param string $connString Stringa di connessione allo storage
         */
        private function __construct(string $storagename, StorageDriver $driver, string $connString){
            $this->storagename = $storagename;
            $this->driver = $driver;
            if($this->driver->connectToStorage($connString)){
                $this->webRoot = $driver->getWebRoot();
            }
        }

        public function mkdir(string $name, string $mode="0777"):bool{
            return $this->driver->mkdir($name, $mode);
        }

        public function rm(string $name):bool{
            return $this->driver->rm($name);
        }

        public function fcopy(string $source,string $target){
            return $this->driver->fcopy($source,$target);
        }

        public function ls(string $dir="./", string $pattern="*.*"):array{
            return $this->driver->ls($dir,$pattern);
        }

        public function fileCompare(string $src, string $dest):bool{
            return $this->driver->fileCompare($src, $dest);
        }

        public function fileExists(string $src):bool{
            return $this->driver->fileExists($src);
        }

        public function fileWrite(string $name, mixed $content, string $mode="a"):bool{
            return $this->driver->fileWrite($name,$content,$mode);
        }

        public function fileRead(string $name):mixed{
            return $this->driver->fileRead($name);
        }

        public function isDir(string $name):bool{
            return $this->driver->isDir($name);
        }

        /**
         * Create new file in storage
         *
         * @param string $name
         * @param mixed $content
         * @return boolean
         */
        public function fileCreate(string $name, mixed $content): bool{
            settype($name,"string");
            
            $source = "{$this->webRoot}{$name}";

            if($this->fileExists($name)){
                $vars = array(
                    FILE => $source
                );
                EExceptionManager::throwException("StorageFileAlreadyExistException", $vars);
                return false;
            } else {

                if($this->fileWrite($name,$content,"wb") && $this->fileExists($name)){
                    return true;
                }
                $vars = array(
                    FILE => $source
                );
                EExceptionManager::throwException("StorageFileNotWritableException", $vars);
                return false;
            }

        }

     }
