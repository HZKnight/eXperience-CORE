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

/**
 *  Experience SPL autoloader. 
 *  This version can load class from Experience Pakages and Vendor directory 
 * 
 *  @author  Luca Liscio <lucliscio@h0model.org>
 *  @version v 2.0 2020/12/09 20:20:00
 *  @copyright &copy;2022 HZKnight 
 *  @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
 *   
 *  @package Experience
 *  @filesource
 */

if(session_id() == ""){
    session_start();
}

putenv("ECORE=v0.2.3-Alfa");

//Path definitions
$_SESSION["experience_path"] = __DIR__.DIRECTORY_SEPARATOR;

$ebase_path = $_SESSION["experience_path"]."Experience";
$evendor_parth = $ebase_path.DIRECTORY_SEPARATOR."vendor";

//Vendor Class map
$vendors = array(
    'LoggerInterface' => $evendor_parth."/Psr/log/LoggerInterface.php",
    'LogLevel' => $evendor_parth."/Psr/log/LogLevel.php",
    'FirePHP' => $evendor_parth."/FirePHPCore/FirePHP.class.php"
);

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

//Second step define SPL autoloader

/**
 * This class return class name
 * @param string $classname The name of the class to load
 */
function getClassName($classname){
    $q = explode("\\", $classname);
    return $q[count($q)-1];
}


/**
 * This method verify if this class is vendor class
 * @param string $classname The name of the class to load
 */
function isVendor($classname){
    global $vendors;
    return array_key_exists(getClassName($classname), $vendors);
}


/**
 * Experience SPL autoloader.
 * @param string $classname The name of the class to load
 */
function experienceAutoload($classname){
    global $vendors, $ebase_path;
    $pathtoclass = "";

    if(isVendor($classname)){
        $pathtoclass = $vendors[getClassName($classname)];
    } else {
        $pathtoclass = str_replace('Experience', '', $classname);
        $pathtoclass = str_replace('\\', DIRECTORY_SEPARATOR, $pathtoclass);
        $pathtoclass = $ebase_path.strtolower($pathtoclass).'.class.php';
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

if(version_compare(PHP_VERSION, '5.1.2', '>=')) {
    //SPL autoloading was introduced in PHP 5.1.2
    if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
        spl_autoload_register('experienceAutoload', true, true);
    } else {
        spl_autoload_register('experienceAutoload');
    }
} else {
    /**
     * Fall back to traditional autoload for old PHP versions
     * @param string $classname The name of the class to load
     */
    function __autoload($classname)
    {
        experienceAutoload($classname);
    }
}
