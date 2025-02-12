<?php

    /*
     * local.class.php
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

    namespace Experience\Core\Io\Storage\Driver;

    use Experience\Core\Io\Storage\Driver\Interface\StorageDriveInterface;
    use Experience\Core\Io\Storage\Exceptions\StorageException;

    /**
     * Driver for local storage (file system)
     *
     * @author  lucliscio <lucliscio@h0model.org>
     * @version v 1.0.0
     * @copyright &copy;2025 HZKnight
     * @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
     *
     * @package eXperience
     * @filesource
     */

    class Local implements StorageDriveInterface{
        
        private string $webRoot;

        public function __construct($path="/"){
            $this->webRoot = getcwd().$path;
        }

        public function getWebRoot(): string{
            return $this->webRoot;
        }

        /**
         * Make a directory
         *
         * @param string $name
         * @param string $mode default 0777
         * @return void
         */
        public function mkdir($name, $mode=0777){
            settype($name,"string");

            $source = $this->webRoot.$name;

            clearstatcache();

            if(!$this->fileExists($source)){
                if (!mkdir($source,$mode)){
                    throw new StorageException("System Error: mkdir(".$source.",".$mode.")");
                }
            } else {
                throw new StorageException("System Error: mkdir(".$source.",".$mode.") - Already exist");
            }
        }


        /**
         * Delete file and directory
         *
         * @param string $name
         * @return void
         */
        public function rm($name){
            settype($name,"string");

            clearstatcache();

            $source = $this->webRoot.$name;

            if(!$this->fileExists($source)){
                throw new StorageException("System Error: rm({$source}) - File not found");
            } elseif(!is_writable($source)){
                throw new StorageException("System Error: rm({$source}) - File not writable");
            } else {
                if(is_dir($source)){
                    $it = new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS);
                    $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
                    foreach($files as $file) {
                        $this->rm($file->getPathname());
                    }
                    rmdir($source);
                } else {
                    unlink($source);
                }

                clearstatcache();
    
                if(!$this->fileExists($source)){
                    return;
                }
                
                throw new StorageException("System Error: rm(".$source.")");
            }
        }


        /**
         * File copy
         *
         * @param string $source
         * @param string $target
         * @return void
         */
        public function fcopy($source,$target){
            settype($source,"string");
            settype($target,"string");
          
            clearstatcache();

            $src = $this->webRoot.$source;
            $dest = $this->webRoot.$target;
          
            if(!$this->fileExists($src)){
                return;
            }elseif(copy($src, $dest)){
                clearstatcache();
          
                if($this->fileExists($dest) && fileCompare($src, $dest)){
                    return;
                }

            }
          
            $this->rm($dest);
            throw new StorageException("System Error: fcopy(".$this->webRoot.$source.",".$this->webRoot.$target.")");
        }


        /**
         * Directory listing
         *
         * @param string $dir default ./
         * @param string $pattern default *.*
         * @return array $ls
         */
        public function ls($dir="./",$pattern="*.*"): array{
            settype($dir,"string");
            settype($pattern,"string");

            $source = $this->webRoot.$dir;

            clearstatcache();

            $ls=array();
            $regexp=str_replace("/\\x5C\\x3F/",".",str_replace("/\\x5C\\x2A/",".*",preg_quote($pattern,"/")));

            if(is_dir($source)){
                $it = new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS);
                $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
                foreach($files as $file) {
                    $fileName = $file->getFilename();
                    if(preg_match("/^".$regexp."$/", $fileName)){
                        array_push($ls, $fileName);
                    }
                }

                sort($ls,SORT_STRING);
                return $ls;
            }
           
            throw new StorageException("System Error: ls(".$source.",".$pattern.")");
        }


        /**
         * Compare 2 files return true if are equals
         *
         * @param string $src
         * @param string $dest
         * @return boolean
         */
        public function fileCompare($src, $dest): bool{
            settype($src,"string");
            settype($dest,"string");

            if($this->fileExists($src) && $this->fileExists($dest)) {
                return md5_file($src) == md5_file($dest) ? true : false;
            } else {
                return false;
            }
        }


        /**
         * Return true if file exist
         *
         * @param string $src
         * @return boolean
         */
        public function fileExists($src): bool{
            return file_exists($src);
        }


        /**
         * Write file
         *
         * @param string $name
         * @param mixed $content
         * @param string $mode default a
         * @return boolean
         */
        public function fileWrite($name,$content,$mode="a"): bool{
            settype($name,"string");

            $source = $this->webRoot.$name;

            if($mode == "a" && !$this->fileExists($source)){
                throw new StorageException("System Error: fileWrite(".$source.",".$content.",".$mode.") File not exist");
            } else {
                if(($fid=fopen($source,$mode))!==false){
                    if(fwrite($fid,$content)===strlen($content)){
                        fflush($fid);
                        fclose($fid);
                        clearstatcache();
                        return true;
                    }
                    @fclose($fid);
                    return false;
                }
            }

        }


        /**
         * Read file
         *
         * @param string $name
         * @return mixed
         */
        public function fileRead($name): mixed{
            settype($name,"string");

            $source = $this->webRoot.$name;

            if($this->fileExists($source)){
                return file_get_contents($source);
            }

            throw new StorageException("System Error: fileRead(".$source."). File not exist");
        }
    }
