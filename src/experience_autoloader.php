<?php

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
 *  Experience SPL autoloader. 
 *  This version can load class from Experience Pakages and Vendor directory 
 * 
 *  @author  Luca Liscio <lucliscio@h0model.org>
 *  @version v 2.0 2020/12/09 20:20:00
 *  @copyright Copyright 2020 HZKnight 
 *  @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
 *   
 *  @package Experience
 *  @filesource
 */

if(session_id() == ""){
    session_start();
}

$_SESSION["experience_path"] = __DIR__.DIRECTORY_SEPARATOR;
putenv("ECORE=v0.2.3-Alfa");

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
 * Experience SPL autoloader.
 * @param string $classname The name of the class to load
 */
function experienceAutoload($classname){

    $pathtoclass = str_replace('Experience', '', $classname);
    $pathtoclass = str_replace('\\', DIRECTORY_SEPARATOR, $pathtoclass);

    $base_path = $_SESSION["experience_path"].'Experience';
    
    //Calcolate path alternative
    $paths = array(
        //Main path
        0 => $base_path.strtolower($pathtoclass).'.class.php',
        1 => $base_path.$pathtoclass.'.class.php',

        //Vendor path
        2 => $base_path.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.strtolower($pathtoclass).'.class.php',
        3 => $base_path.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.$pathtoclass.'.php',
        4 => $base_path.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.$pathtoclass.'.class.php',
        5 => $base_path.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.strtolower($pathtoclass).'.php'
    );

    echo "<pre>";
    var_dump($paths);

    foreach ($paths as &$filename) {
        if(file_exists($filename)){
            if (is_readable($filename)) {
                require $filename;
                return;
            } else {
                throw new Exception("Unable to load file: ".$filename);
            }
        }
    }

    throw new Exception("Unable to find class: ".$pathtoclass);

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
