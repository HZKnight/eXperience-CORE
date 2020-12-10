<?php
	namespace Experience\Core\Logger\Exceptions;
	
	use Experience\Exceptions\EException;
	
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
	 *  questa eccezione si verifica quando si prova a richiamare un appender che non esiste
	 *
	 *  @author  Luca Liscio <lucliscio@h0model.org>
	 *  @version 0.0.3 2020/11/29 21:41:20
	 *  @copyright 2020 HZKnight
	 *  @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
	 *
	 *  @package Experience
	 *  @subpackage Core\Logger\Exception
	 *  @filesource
	 */
	 
	class AppenderNotFoundException extends EException {
            protected $code = "L01"; 
        }
?>