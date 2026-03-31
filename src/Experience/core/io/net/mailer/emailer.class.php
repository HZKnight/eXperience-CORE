<?php
    /*
     * emailer.class.php
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
     * Lincense
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


    namespace Experience\Core\Io\Net\Mailer;

    // Namespace alias
    use Experience\Core\Tools\Config\EConfigManager;
    use Experience\Core\Io\Net\Mailer\EMessage;
    
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    /**
     * Classe per l'invio di email basata su PHPMailer 6.8.0
     *
     * @author  Luca Liscio <lucliscio@h0model.org>
     * @version 1.2.0
     * @copyright 2023-2025 HZKnight
     * @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
     *
     * @package eXperience
     * @subpackage Core\Io\Net\Mailer
     *
     * @filesource
     */

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
         * Costruttore
         *
         * @param EConfigManager $conf
         * @return void
         */
        public function __construct(EConfigManager $conf){
            $this->mailer = new PHPMailer(true);
            $this->config = $conf;
            $this->mailer->SMTPDebug = SMTP::DEBUG_OFF;
            if($this->config->getParam('mail.is_smtp')){
                $this->enableSMTP();
            }
        }

        /**
         * Enable SMTP
         *
         * @return void
         */
        public function enableSMTP(){
            $this->mailer->isSMTP(); // Set mailer to use
            $this->mailer->Host       = $this->config->getParam('mail.smtp_host'); // Specify main and backup SMTP servers
            $this->mailer->Port       = $this->config->getParam('mail.smtp_port'); // TCP port to connect
            $this->mailer->SMTPAuth   = $this->config->getParam('mail.smtp_auth'); // Enable SMTP authentication

            if($this->mailer->SMTPAuth){
                $this->mailer->Username   = $this->config->getParam('mail.smtp_username'); // SMTP username
                $this->mailer->Password   = $this->config->getParam('mail.smtp_passwd'); // SMTP password
                $this->mailer->SMTPSecure = $this->config->getParam('mail.smtp_secure'); // Enable TLS encryption, `ssl` also accepted
            }
        }

        /**
         * Add attachment to email
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

        /**
         * Send email message
         *
         * @param EMessage $message
         * @return void
         */
        public function send(EMessage $message){

            try {
                //Recipients
                $this->mailer->setFrom($this->config->getParam('mail.sender_email'), $this->config->getParam('mail.sender_name'));
            
                foreach ($message->getAddress() as &$value) {
                    $this->mailer->addAddress($value);//Add a recipient
                }
                unset($value);

                foreach ($message->getCC() as &$value) {
                    $this->mailer->addCC($value);
                }
                unset($value);

                foreach ($message->getBCC() as &$value) {
                    $this->mailer->addBCC($value);
                }
                unset($value);
            
                //Attachments
                foreach ($message->getAttachments() as &$value) {
                    $this->addAttachment(basename($value), $value);
                }

                //Content
                $this->mailer->isHTML($message->isHTML); //Set email format to HTML
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
