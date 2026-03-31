<?php
    /*
     * storagedriver.class.php
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

    use Experience\Core\Io\Storage\Driver\Interface\StorageDriverInterface;
    use Experience\Core\Exceptions\EExceptionManager;

    /**
     * Abstract class for storage driver
     *
     * @author  lucliscio <lucliscio@h0model.org>
     * @version 1.0.0
     * @copyright &copy;2025 HZKnight
     * @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
     *
     * @package eXperience
     * @subpackage Core\Io\Storage\Driver
     *
     * @filesource
     */

    abstract class StorageDriver implements StorageDriverInterface{

        public EExceptionManager $exceptionManager;
        private string $webRoot;

        /**
         * Costruttore
         */
        public function __construct(){
            global $environment;

            $this->webRoot = "";

            //Exception definition
            if(is_array($environment) && isset($environment['emanager'])){
                $this->exceptionManager = $environment['emanager'];
            } else {
                $this->exceptionManager = EExceptionManager::getExceptionManager();
            }

            $this->exceptionManager->addException("StorageConnectionException", dgettext("Elang","Connection to storage failed"), "SE001");
            $this->exceptionManager->addException("StorageDirectoryNotCreatedException", dgettext("Elang","Directoty [DIR] with mode [MODE] not created"), "SE002");
            $this->exceptionManager->addException("StorageDirectoryAlreadyExistException", dgettext("Elang","Directoty [DIR] with mode [MODE] already exist"), "SE003");
            $this->exceptionManager->addException("StorageFileNotFoundException", dgettext("Elang","File [FILE] not found"), "SE004");
            $this->exceptionManager->addException("StorageFileNotWritableException", dgettext("Elang","File [FILE] not writable"), "SE005");
            $this->exceptionManager->addException("StorageCopyException", dgettext("Elang","Copy [SOURCE] to [TARGET] filed"), "SE006");
            $this->exceptionManager->addException("StorageFileListingException", dgettext("Elang","System Error: ls ([SOURCE], [PATTERN])"), "SE007");
            $this->exceptionManager->addException("StorageFileAlreadyExistException", dgettext("Elang","File [FILE] already exist"), "SE008");
        }

        abstract public function connectToStorage($path):bool;
        abstract public function getWebRoot(): string;
        abstract public function mkdir($name, $mode=0777);
        abstract public function rm($name):bool;
        abstract public function fcopy($source,$target);
        abstract public function ls($dir="./",$pattern="*.*"):array;
        abstract public function fileCompare($src, $dest):bool;
        abstract public function fileExists($src):bool;
        abstract public function fileWrite($name,$content,$mode="a"):bool;
        abstract public function fileRead($name):mixed;
        abstract public function isDir($name):bool;

    }
