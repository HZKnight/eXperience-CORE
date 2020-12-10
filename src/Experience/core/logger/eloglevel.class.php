<?php
    namespace Experience\Core\Logger;

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
	 *  Log levels definition
	 *
	 *  @author  Luca Liscio <lucliscio@h0model.org>
	 *  @version 0.0.1 2020/12/03 01:44:20
	 *  @copyright 2020 HZKnight
	 *  @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
	 *
	 *  @package Experience
	 *  @subpackage Core\Logger
	 *  @filesource
	 */

    /**
     * Describes log levels.
     */
    class ELogLevel
    {
        const EMERGENCY = 407;
        const ALERT     = 406;
        const CRITICAL  = 405;
        const ERROR     = 404;
        const WARNING   = 403;
        const NOTICE    = 402;
        const INFO      = 401;
        const DEBUG     = 400;
    }
