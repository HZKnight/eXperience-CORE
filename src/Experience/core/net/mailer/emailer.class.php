<?php

    namespace Experience\Core\EMailer;

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
     *  Semplice classe per l'invio di email 
     *
     *  @author  Luca Liscio <lucliscio@h0model.org>
     *  @version 0.0.2 2020/11/29 21:03:20
     *  @copyright 2020 HZKnight
     *  @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
     *
     *  @package Experience
     *  @subpackage Core
     *  @filesource
     */

    /* Namespace alias. */
    use Experience\Core\config\EConfigManager;
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    class EMailer {
        
        // Message type
    	/** Simple text */
        const TEXT = 601;
        /** HTML text */
        const HTML = 602;

        // Mailer
        private $mailer;

        // configurazine
        private $config;

        /**
         * Undocumented function
         *
         * @param EConfigManager $conf
         */
        public function __construct(EConfigManager $conf){
            $this->mailer = new PHPMailer(TRUE);
            $this->config = $conf;  
            $this->mailer->SMTPDebug = 2;                               // Enable verbose debug output         
        }

        /**
         * Undocumented function
         *
         * @return void
         */
        public function enableSMTP(){
            $this->mailer->isSMTP();                                            // Set mailer to use 
            $this->mailer->Host       = $this->config->get_param('smtp_host');  // Specify main and backup SMTP servers
            $this->mailer->Port       = $this->config->get_param('smtp_port');  // TCP port to connect 
            $this->mailer->SMTPAuth   = $this->config->get_param('smtp_auth');  // Enable SMTP authentication

            if($this->mailer->SMTPAuth){
                $this->mailer->Username   = $this->config->get_param('smtp_username');  // SMTP username
                $this->mailer->Password   = $this->config->get_param('smtp_passwd');    // SMTP password
                $this->mailer->SMTPSecure = $this->config->get_param('smtp_secure');    // Enable TLS encryption, `ssl` also accepted
            }       
        }

        public function send($message, $type){

        }
    }
