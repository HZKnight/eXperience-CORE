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
     *  Classe che rappresenta il messaggio da inviare 
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

     class EMessage {

        private $address;
        private $cc;
        private $bcc;
        private $attachments;
        public $isHTML;
        private $subject;
        private $body;

        public function __construct(){
            $this->address = array();
            $this->cc = array();
            $this->bcc = array();
            $this->attachments = array();
            $this->isHTML = false;
            $this->subject = "";
            $this->body = "";
        }
       
        public function addAddres(string $addres){
            $this->address[] = $addres;
        }

        public function addCC(string $addres){
            $this->cc[] = $addres;
        }

        public function addBCC(string $addres){
            $this->bcc[] = $addres;
        }

        public function addAttachment(string $attachment){
            $this->attachments[] = $attachment;
        }

        public function setSubject(string $subject){
            $this->subject = $subject;
        }

        public function setBody($body){
            $this->body = $body;
        }

        public function getAddress() {
            return $this->address;
        }

        public function getCC() {
            return $this->cc;
        }

        public function getBCC() {
            return $this->bcc;
        }

        public function getAttachments() {
            return $this->attachments;
        }

        public function getSubject(){
            return $this->subject;
        }

        public function getBody(){
            return $this->body;
        }        

     }


