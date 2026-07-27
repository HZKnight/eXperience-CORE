<?php

    /*
     * localstoragedriver.class.php
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

    namespace Experience\Core\Io\Storage\Driver;

    use Experience\Core\Io\Storage\Driver\Interface\StorageDriverInterface;
    use Experience\Core\Io\Storage\Driver\StorageDriver;
    use Experience\Core\Exceptions\EExceptionManager;

    use \RecursiveDirectoryIterator;
    use \RecursiveIteratorIterator;


    // Costanti per i segnaposto nei messaggi delle accezioni
    define("FILE", "[FILE]");
    define("SOURCE", "[SOURCE]");
    define("TARGET", "[TARGET]");
    define("DIR", "[DIR]");
    define("MODE", "[MODE]");
    define("PATTERN", "[PATTERN]");

    /**
     * Driver for local storage (file system)
     *
     * @author  lucliscio <lucliscio@h0model.org>
     * @version 2.1.0
     * @copyright &copy;2026 HZKnight
     * @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
     *
     * @package eXperience
     * @subpackage Core\Io\Storage\Driver
     *
     * @filesource
     */

    class LocalStorageDriver extends StorageDriver implements StorageDriverInterface {
        
        private string $webRoot;


        /**
         * Costruttore
         */
        public function __construct(){
            parent::__construct();
            $this->webRoot = "";
        }


        /**
         * Connessione allo storage locale
         * @param mixed $path
         * @return bool
         */
        public function connectToStorage(mixed $path): bool{
            // Rimuove eventuali slash iniziali/finali dal path e ne aggiunge esattamente uno all'inizio e uno alla fine
            $normalizedPath = '/' . trim((string)$path, '/\\') . '/';
            
            $fullPath = rtrim(getcwd(), '/\\') . $normalizedPath;

            if (!is_dir($fullPath)) {
                EExceptionManager::throwException("StorageConnectionException");
                return false;
            }

            $this->webRoot = $fullPath;
            return true;
        }


        /**
         * Summary of getWebRoot
         * @return string
         */
        public function getWebRoot(): string{
            return $this->webRoot;
        }


        /**
         * Make a directory
         *
         * @param string $name
         * @param string $mode default 0777
         * @return boolean
         */
        public function mkdir(string $name, string $mode="0777"): bool{
            settype($name,"string");
            settype($mode,"string");

            $source = $this->webRoot.$name;

            clearstatcache();

            if(!$this->fileExists($source)){
                // Conversione esplicita da stringa ottale ad int ottale
                $octalMode = octdec($mode);

                if (!mkdir($source, $octalMode, true)) { // Usa $octalMode
                    $vars = array(
                        DIR => $source,
                        MODE => $mode
                    );
                    EExceptionManager::throwException("StorageDirectoryNotCreatedException", $vars);
                    return false;
                }
            } else {
                $vars = array(
                    DIR => $source,
                    MODE => $mode
                );
                EExceptionManager::throwException("StorageDirectoryAlreadyExistException", $vars);
                return false;
            }

            return true;
        }


        /**
         * Delete file and directory
         *
         * @param string $name
         * @return void
         */
        public function rm(string $name): bool{
            settype($name,"string");

            $fullPath = $this->getFullPath($name);

            // Deve verificare file_exists (che in PHP restituisce true sia per file che per directory)
            if (!file_exists($fullPath)) {
                EExceptionManager::throwException("StorageFileNotFoundException", ["FILE" => $fullPath]);
                return false;
            }

            if (is_dir($fullPath)) {
                return $this->deleteDirectoryRecursive($fullPath);
            }

            return @unlink($fullPath);
        }


        /**
         * File copy
         *
         * @param string $source
         * @param string $target
         * @return boolean
         */
        public function fcopy(string $source, string $target): bool{
            settype($source,"string");
            settype($target,"string");
          
            clearstatcache();

            $fullSource = $this->getFullPath($source);
            $fullDest = $this->getFullPath($target);

            if (!file_exists($fullSource)) {
                EExceptionManager::throwException("StorageFileNotFoundException", ["FILE" => $fullSource]);
                return false;
            }

            // Assicuriamoci che la cartella contenitrice della destinazione esista
            $destDir = dirname($fullDest);
            if (!is_dir($destDir)) {
                mkdir($destDir, 0777, true);
            }

            $result = @copy($fullSource, $fullDest);

            if (!$result) {
                EExceptionManager::throwException("StorageFileCopyException", [
                    "SOURCE" => $fullSource,
                    "DESTINATION" => $fullDest
                ]);
                return false;
            }

            return true;
        }


        /**
         * Directory listing
         *
         * @param string $dir default ./
         * @param string $pattern default *.*
         * @return array $ls
         */
        public function ls(string $dir="./", string $pattern="*.*"): array{
            settype($dir,"string");
            settype($pattern,"string");

            // Se la directory passa come stringa vuota, usiamo la webRoot configurata
            $targetDir = $this->getFullPath($dir);

            if (!is_dir($targetDir)) {
                EExceptionManager::throwException("StorageFileListingException", [
                    "SOURCE" => $targetDir,
                    "PATTERN" => $pattern
                ]);
                return [];
            }

            // Aggiunge lo slash finale prima del pattern se non presente
            $searchPattern = rtrim($targetDir, '/\\') . '/' . $pattern;
            $matches = glob($searchPattern);

            if ($matches === false) {
                EExceptionManager::throwException("StorageFileListingException", [
                    "SOURCE" => $targetDir,
                    "PATTERN" => $pattern
                ]);
                return [];
            }

            // Restituisce solo i nomi relativi/basename dei file trovati
            $result = [];
            foreach ($matches as $filePath) {
                $result[] = basename($filePath);
            }

            return $result;
        }


        /**
         * Compare 2 files return true if are equals
         *
         * @param string $src
         * @param string $dest
         * @return boolean
         */
        public function fileCompare(string $src, string $dest): bool{
            settype($src,"string");
            settype($dest,"string");

            if($this->fileExists($src) && $this->fileExists($dest)) {
                return md5_file($this->webRoot.$src) == md5_file($this->webRoot.$dest) ? true : false;
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
        public function fileExists(string $src): bool{
            settype($src,"string");
            $source = $this->getFullPath($src);
            return file_exists($source);
        }


        /**
         * Write file
         *
         * @param string $name
         * @param mixed $content
         * @param string $mode default a
         * @return boolean
         */
        public function fileWrite(string $name, mixed $content, string $mode="a"): bool{
            settype($name,"string");
            settype($content,"string");
            settype($mode,"string");

            $source = "{$this->webRoot}{$name}";

            if($mode == "a" && !$this->fileExists($name)){
                $vars = array(
                    FILE => $source
                );
                EExceptionManager::throwException("StorageFileNotFoundException", $vars);
            } elseif(($fid=fopen($source,$mode))!==false){
                if(fwrite($fid,$content)===strlen($content)){
                    fflush($fid);
                    fclose($fid);
                    clearstatcache();
                    return true;
                }
                @fclose($fid);
            }
            return false;
        }


        /**
         * Read file
         *
         * @param string $name
         * @return mixed
         */
        public function fileRead(string $name): mixed{
            settype($name,"string");

            $source = "{$this->webRoot}{$name}";

            if($this->fileExists($name)){
                return file_get_contents($source);
            }

            $vars = array(
                FILE => $source
            );
            EExceptionManager::throwException("StorageFileNotFoundException", $vars);
            return false;
        }


        /**
         * Verify if is a directory
         *
         * @param string $name
         * @return boolean
         */
        public function isDir(string $name): bool{
            settype($name,"string");
            $source = $this->getFullPath($name);
            return is_dir($source);
        }


        private function getFullPath(string $path): string {
            // Se il path è vuoto o '.', restituisce direttamente la root
            $trimmed = trim($path, '/\\');
            if ($trimmed === '' || $trimmed === '.') {
                return $this->webRoot;
            }
            return $this->webRoot . $trimmed;
        }


        private function deleteDirectoryRecursive(string $dir): bool {
            $items = array_diff(scandir($dir) ?: [], ['.', '..']);
            foreach ($items as $item) {
                $path = $dir . DIRECTORY_SEPARATOR . $item;
                is_dir($path) ? $this->deleteDirectoryRecursive($path) : @unlink($path);
            }
            return @rmdir($dir);
        }

    }
