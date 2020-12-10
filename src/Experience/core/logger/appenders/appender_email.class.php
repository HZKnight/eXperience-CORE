<?php

     namespace HZSystem\Core\Logger\Appenders;
     
     use Experience\Core\Exceptions\ENotApplicableMethodException;
        
     use Experience\Core\Logger\ELogger;
     use Experience\Core\Logger\ELogRow;
     use Experience\Core\net\mailer\EMailer;
     
	
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
     *  File appender per ELogger 
     *
     *  @author  Luca Liscio <lucliscio@h0model.org>
     *  @version 0.0.2 2020/11/29 21:16:20
     *  @copyright 2020 HZKnight
     *  @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
     *
     *  @package Experience
     *  @subpackage Core\Logger\Appenders
     *  @filesource
     */
	class Appender_email extends Appender {
		
	     /**
           * Send mail width log row
           * 
           * @param ELogRow $log_row
           */
		public function add(ELogRow $log_row){
			
		}
		
		/**
           * Return a part of log file
           * 
           * @param integer $start start row
           * @param integer $stop end row
           * @return list of log row
           * @throws LogFileNotFoundExceprions 
           */
		public function get_log($start=0,$stop){
			throw new ENotApplicableMethodException(dgettext("Elang","Method not applicable"));	
		}
		
	}