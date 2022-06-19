<?php

    /*
     *                                        _____                      _                     
     *                                       | ____|_  ___ __   ___ _ __(_) ___ _ __   ___ ___ 
     *                                       |  _| \ \/ / '_ \ / _ \ '__| |/ _ \ '_ \ / __/ _ \
     *                                       | |___ >  <| |_) |  __/ |  | |  __/ | | | (_|  __/
     *                                       |_____/_/\_\ .__/ \___|_|  |_|\___|_| |_|\___\___|
     *                                                  |_| HZKnight free PHP Scripts 
     *
     *                                             lucliscio <lucliscio@h0model.org>, ITALY
     * 
     * -------------------------------------------------------------------------------------------
     * Licence
     * -------------------------------------------------------------------------------------------
     *
     * Copyright (C) 2022 HZKnight
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


    namespace Experience\Core\EMailer;

	
    /**
     *  Semplice classe per l'invio di email 
     *
     *  @author  Luca Liscio <lucliscio@h0model.org>
     *  @version 0.0.2 2020/11/29 21:03:20
     *  @copyright 2022 HZKnight
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

        /**
         * Undocumented function
         *
         * @param string $name
         * @param string $path
         * @return boolean
         */
        public function addAttachment(string $name, string $path){
            return $this->mailer->addAttachment($path, $name); 
        }

        public function send(string $from, $message, $type){
            //Recipients
    $mail->setFrom('from@example.com', 'Mailer');
    $mail->addAddress('joe@example.net', 'Joe User');     //Add a recipient
    $mail->addAddress('ellen@example.com');               //Name is optional
    $mail->addReplyTo('info@example.com', 'Information');
    $mail->addCC('cc@example.com');
    $mail->addBCC('bcc@example.com');

    
    //Content
    $mail->isHTML(true);                                  //Set email format to HTML
    $mail->Subject = 'Here is the subject';
    $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    $mail->send();
        }
    }
