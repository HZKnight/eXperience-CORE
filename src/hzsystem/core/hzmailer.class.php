<?php

    namespace HZSystem\Core\HZMailer;

    /*
     * Copyright (C) 2019 Luca Liscio
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
     *  Semplice classe per l'invio di email 
     *
     *  @author  Luca Liscio <lucliscio@h0model.org>
     *  @version 0.0.1 2019/08/25 16:03:20
     *  @copyright 2019 Luca Liscio
     *  @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
     *
     *  @package hzSystem
     *  @subpackage Core\Logger
     *  @filesource
     */
    
    require($_SESSION["hzSystem_path"].str_replace('/', DIRECTORY_SEPARATOR,'hzsystem/libs/mailer/Easy_Mail.class.php'));

    class HZMailer {
        
        // Message type
    	/** Simple text */
        const TEXT = 601;
        /** HTML text */
        const HTML = 602;

        // Mailer
        private $mailer;

        public function __construct($from, $to, $subject, $return){
            $this->mailer = &new Easy_Email($from, $to, $subject, $return);
        }

        public function send($message, $type){

        }
    }
