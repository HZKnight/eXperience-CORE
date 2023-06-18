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
     * Copyright (C) 2023 HZKnight
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


    namespace Experience\Core\Net\Mailer;

	
    /**
     *  Classe per l'invio di email basata su PHPMailer 6.8.0
     *
     *  @author  Luca Liscio <lucliscio@h0model.org>
     *  @version 1.0.0 2023/06/10 09:15:20
     *  @copyright 2023 HZKnight
     *  @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
     *
     *  @package Experience
     *  @subpackage Core
     *  @filesource
     */

    // Namespace alias
    use Experience\Core\Logger\ELogger;
    use Experience\Core\Logger\ELogLevel;
    use Experience\Core\Config\EConfigManager;
    use Experience\Core\Net\Mailer\EMessage;
    
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
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

        // logger
        private $log;

        /**
         * Undocumented function
         *
         * @param EConfigManager $conf
         */
        public function __construct(EConfigManager $conf){
            $this->mailer = new PHPMailer(true);
            $this->config = $conf; 
            $this->mailer->SMTPDebug = SMTP::DEBUG_OFF;
            if($this->config->get_param('mail.is_smtp')){
                $this->enableSMTP();
            }                               // Enable verbose debug output         
        }

        /**
         * Undocumented function
         *
         * @return void
         */
        public function enableSMTP(){
            $this->mailer->isSMTP();                                            // Set mailer to use 
            $this->mailer->Host       = $this->config->get_param('mail.smtp_host');  // Specify main and backup SMTP servers
            $this->mailer->Port       = $this->config->get_param('mail.smtp_port');  // TCP port to connect 
            $this->mailer->SMTPAuth   = $this->config->get_param('mail.smtp_auth');  // Enable SMTP authentication

            if($this->mailer->SMTPAuth){
                $this->mailer->Username   = $this->config->get_param('mail.smtp_username');  // SMTP username
                $this->mailer->Password   = $this->config->get_param('mail.smtp_passwd');    // SMTP password
                $this->mailer->SMTPSecure = $this->config->get_param('mail.smtp_secure');    // Enable TLS encryption, `ssl` also accepted
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
            if($name == ""){
                $this->mailer->addAttachment($path); 
            } else {
                $this->mailer->addAttachment($path, $name); 
            }
        }

        public function send(EMessage $message){

            try {
                //Recipients
                $this->mailer->setFrom($this->config->get_param('mail.sender_email'), $this->config->get_param('mail.sender_name'));
            
                foreach ($message->getAddress() as &$value) {
                    $this->mailer->addAddress($value);     //Add a recipient
                }

                foreach ($message->getCC() as &$value) {
                    $this->mailer->addCC($value);
                }

                foreach ($message->getBCC() as &$value) {
                    $this->mailer->addBCC($value);
                }
            
                //Attachments
                foreach ($message->getAttachments() as &$value) {
                    $this->addAttachment(basename($value), $value);
                }
    
                //Content
                $this->mailer->isHTML($message->isHTML);                                  //Set email format to HTML
                $this->mailer->Subject = $message->getSubject();
                $this->mailer->Body    = $message->getBody();

                if($message->isHTML){
                    $this->mailer->AltBody = 'The mail body is in html and it is not plain text, this mail client does not support html';
                }

                $this->mailer->send();

                return "";

            } catch (Exception $e) {
                return "Message could not be sent. Mailer Error: {$this->mailer->ErrorInfo}";
            }
        }
    }
