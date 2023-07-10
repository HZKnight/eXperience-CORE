<?php

/*
 * experience_autoloader.php
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
 * Copyright (C) 2021 HZKnight
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
 *  Experience SPL autoloader. 
 *  This version can load class from Experience Pakages and Vendor directory 
 * 
 *  @author  Luca Liscio <lucliscio@h0model.org>
 *  @version v 2.0 2020/12/09 20:20:00
 *  @copyright Copyright 2021 HZKnight 
 *  @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
 *   
 *  @package Experience
 *  @filesource
 */

if(session_id() == ""){
    session_start();
}

class EAutoloader{

    private $ebase_path;
    private $evendor_path;
    private $vendors;

    public function __construct(){

        putenv("ECORE=v0.2.3-Alfa");

        //Path definitions
        $_SESSION["experience_path"] = __DIR__.DIRECTORY_SEPARATOR;

        $this->ebase_path = $_SESSION["experience_path"]."Experience";
        $this->evendor_path = $this->ebase_path.DIRECTORY_SEPARATOR."vendor";

        $this->registerVendor();

        //First step set internazionalizzation

        //default language
        $language = "it_IT";

        //check current language
        if(getenv("LANG")!=null){
            $language = getenv("LANG");
        } else {
            putenv("LANG=".$language);
        }
            
        setlocale(LC_ALL, $language);

        $lang_path_base_path = $_SESSION["experience_path"]."Experience".DIRECTORY_SEPARATOR."lang";
        $lang_path = $lang_path_base_path.DIRECTORY_SEPARATOR."core"; //Core Language
        bindtextdomain("ELang", $lang_path);

    }

    /**
     * Undocumented function
     *
     * @return void
     */
    static public function register(){

        //Define SPL autoloader
        if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
            spl_autoload_register(array(new self, 'experienceAutoload'), true, true);
        } else {
            spl_autoload_register(array(new self, 'experienceAutoload'));
        }

    }


    private function registerVendor(){
        //Vendor Class map
        $this->vendors = array(
            'Psr\Log\LoggerInterface' => $this->evendor_path."/Psr/log/LoggerInterface.php",
            'Psr\Log\LogLevel' => $this->evendor_path."/Psr/log/LogLevel.php",
            'PHPMailer\PHPMailer\PHPMailer' => $this->evendor_path."/PHPMailer/PHPMailer.php",
            'PHPMailer\PHPMailer\SMTP' => $this->evendor_path."/PHPMailer/SMTP.php",
            'PHPMailer\PHPMailer\Exception' => $this->evendor_path."/PHPMailer/Exception.php"
        );
    }


    /**
     * This method verify if this class is vendor class
     * @param string $classname The name of the class to load
     */
    private function isVendor($classname){
        return array_key_exists($classname, $this->vendors);
    }


    /**
     * Experience SPL autoloader.
     * @param string $classname The name of the class to load
     */
    private function experienceAutoload($classname){
        $pathtoclass = "";

        if($this->isVendor($classname)){
            $pathtoclass = $this->vendors[$classname];
        } else if(0 == strpos($classname, "Experience")){
            $pathtoclass = str_replace('Experience', '', $classname);
            $pathtoclass = str_replace('\\', DIRECTORY_SEPARATOR, $pathtoclass);
            $pathtoclass = $this->ebase_path.strtolower($pathtoclass).'.class.php';            
        } else {
            return;
        }

        if(file_exists($pathtoclass)){
            if(is_readable($pathtoclass)) {
                require $pathtoclass;
                return;
            } else {
                throw new Exception("Unable to load file: ".$pathtoclass);
            }
        }

        throw new Exception("Unable to find class: ".$classname);
    }

}