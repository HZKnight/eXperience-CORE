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
    use Experience\Core\Exceptions\EExceptionManager;

    /**
     * Abstract class for storage driver
     *
     * @author  lucliscio <lucliscio@h0model.org>
     * @version 1.0.0
     * @copyright &copy;2026 HZKnight
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
            EExceptionManager::addException("StorageConnectionException", dgettext("Elang","Connection to storage failed"), "SE001");
            EExceptionManager::addException("StorageDirectoryNotCreatedException", dgettext("Elang","Directoty [DIR] with mode [MODE] not created"), "SE002");
            EExceptionManager::addException("StorageDirectoryAlreadyExistException", dgettext("Elang","Directoty [DIR] with mode [MODE] already exist"), "SE003");
            EExceptionManager::addException("StorageFileNotFoundException", dgettext("Elang","File [FILE] not found"), "SE004");
            EExceptionManager::addException("StorageFileNotWritableException", dgettext("Elang","File [FILE] not writable"), "SE005");
            EExceptionManager::addException("StorageCopyException", dgettext("Elang","Copy [SOURCE] to [TARGET] filed"), "SE006");
            EExceptionManager::addException("StorageFileListingException", dgettext("Elang","System Error: ls ([SOURCE], [PATTERN])"), "SE007");
            EExceptionManager::addException("StorageFileAlreadyExistException", dgettext("Elang","File [FILE] already exist"), "SE008");
        }

        abstract public function connectToStorage(mixed $path):bool;
        abstract public function getWebRoot(): string;
        abstract public function mkdir(string $name, string $mode="0777"):bool;
        abstract public function rm(string $name):bool;
        abstract public function fcopy(string $source, string $target):bool;
        abstract public function ls(string $dir="./", string $pattern="*.*"):array;
        abstract public function fileCompare(string $src, string $dest):bool;
        abstract public function fileExists(string $src):bool;
        abstract public function fileWrite(string $name, mixed $content, string $mode="a"):bool;
        abstract public function fileRead(string $name):mixed;
        abstract public function isDir(string $name):bool;

    }
