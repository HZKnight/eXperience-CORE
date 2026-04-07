<?php
    /*
     * storageDriveInterface.class.php
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

    namespace Experience\Core\Io\Storage\Driver\Interface;

    /**
     * Definizione delle api dei driver per lo storage
     *
     * @author  lucliscio <lucliscio@h0model.org>
     * @version 1.3.2
     * @copyright &copy;2025 HZKnight
     * @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
     *
     * @package eXperience
     * @subpackage Core\Io\Storage\Driver\Interface
     *
     * @filesource
     */

    interface StorageDriverInterface {
        public function connectToStorage(mixed $path):bool;
        public function getWebRoot(): string;
        public function mkdir(string $name, string $mode="0777"):bool;
        public function rm(string $name):bool;
        public function fcopy(string $source, string $target):bool;
        public function ls(string $dir="./", string $pattern="*.*"):array;
        public function fileCompare(string $src, string $dest):bool;
        public function fileExists(string $src):bool;
        public function fileWrite(string $name, mixed $content, string $mode="a"):bool;
        public function fileRead(string $name):mixed;
        public function isDir(string $name):bool;
    }
