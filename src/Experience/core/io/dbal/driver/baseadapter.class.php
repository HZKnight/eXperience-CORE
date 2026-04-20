<?php
    /*
     * BaseAdapter.class.php
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

    namespace Experience\Core\Io\Dbal\Driver;

    use Experience\Core\Io\Dbal\Driver\Interface\DatabaseAdapterInterface;

    /**
     * Abstract class for database adapter
     *
     * @author  lucliscio <lucliscio@h0model.org>
     * @version 1.0.0
     * @copyright &copy;2026 HZKnight
     * @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
     * @package eXperience
     * @subpackage Core\Io\Dbal\Driver
     *
     * @filesource
     */

    abstract class BaseAdapter implements DatabaseAdapterInterface{

        protected ?string $error = null;

        public function __construct() {
            // Costruttore vuoto, le classi figlie possono sovrascriverlo se necessario
        }


        /**
         * Distruttore dell'adapter PDO, chiude la connessione al database
         */
        public function __destruct() {
            $this->disconnect();
         }
         

        abstract public function connect(): bool;
        abstract public function execute(string $sql, ?array $params = []): int|false; // Per INSERT/UPDATE/DELETE
        abstract public function fetchAll(string $sql, ?array $params = []): ?array;
        abstract public function fetchOne(string $sql, ?array $params = []): ?array;
        abstract public function fetchColumn(string $sql, ?array $params = [], int $columnOffset = 0): mixed;
        abstract public function lastInsertId();
        abstract public function beginTransaction();
        abstract public function commit();
        abstract public function rollBack();
        abstract public function disconnect();
}
        
