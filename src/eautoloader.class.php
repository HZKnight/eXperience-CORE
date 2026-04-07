<?php

/*
 * eautoloader.class.php
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
 * Licence
 * -------------------------------------------------------------------------------------------
 *
 * Copyright (C) 2026 HZKnight
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

 if(session_id() == ""){
    session_start();
 }


 /**
  * Summary of AutoloaderException
  */
 class AutoloaderException extends Exception {
    protected $code = "AE001";
 }


/**
 * Experience SPL autoloader.
 * This version can load class from Experience Pakages and Vendor directory
 *
 * @author Luca Liscio <lucliscio@h0model.org>
 * @version 2.0.1
 * @copyright &copy;2021-2026 HZKnight
 * @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
 *
 * @package eXperience
 * @filesource eautoloader.class.php
 */
class EAutoloader{

    private $ebasePath;
    private $evendorPath;
    private $vendors;

    public function __construct(){

        putenv("ECORE=v0.2.3-Alfa");

        //Path definitions
        $_SESSION["experience_path"] = __DIR__.DIRECTORY_SEPARATOR;

        $this->ebasePath = $_SESSION["experience_path"]."Experience";
        $this->evendorPath = $this->ebasePath.DIRECTORY_SEPARATOR."vendor";

        $this->registerVendor();

        //First step set internazionalizzation

        //default language
        $language = "it_IT";

        //check current language
        if(getenv("LANG")!=null){
            $language = getenv("LANG");
        } else {
            putenv("LANG=$language");
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
    public static function register(){

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
            'Psr\Log\LoggerInterface' => "{$this->evendorPath}/Psr/Log/LoggerInterface.php",
            'Psr\Log\LogLevel' => "{$this->evendorPath}/Psr/Log/LogLevel.php",
            'Psr\Log\LoggerAwareInterface' => "{$this->evendorPath}/Psr/Log/LoggerAwareInterface.php",
            'PHPMailer\PHPMailer\PHPMailer' => "{$this->evendorPath}/PHPMailer/PHPMailer.php",
            'PHPMailer\PHPMailer\SMTP' => "{$this->evendorPath}/PHPMailer/SMTP.php",
            'PHPMailer\PHPMailer\Exception' => "{$this->evendorPath}/PHPMailer/Exception.php"
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
        } elseif(0 === strpos($classname, "Experience")){
            $pathtoclass = str_replace('Experience', '', $classname);
            $pathtoclass = str_replace('\\', DIRECTORY_SEPARATOR, $pathtoclass);
            $pathtoclass = $this->ebasePath.strtolower($pathtoclass).'.class.php';
        } else {
            return;
        }

        if(file_exists($pathtoclass)){
            if(is_readable($pathtoclass)) {
                require_once $pathtoclass;
                return;
            } else {
                throw new AutoloaderException("Unable to load file: \"$pathtoclass\"");
            }
        }

        throw new AutoloaderException("Unable to find class: \"$classname\" in path: \"$pathtoclass\"");
    }

}

EAutoloader::register();
