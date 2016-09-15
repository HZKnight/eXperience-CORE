<?php

/*
 * Copyright (C) 2016 Luca
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
 *  hzSystem SPL autoloader.
 * 
 *  @author  Luca Liscio <hzkight@h0model.org>
 *  @version v 1.0 2016/09/09 17:03:20
 *  @copyright Copyright 2016 Luca Liscio 
 *  @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
 *   
 *  @package hzSystem
 *  @filesource
 */

session_start();

$_SESSION["hzSystem_path"] = dirname(__DIR__).DIRECTORY_SEPARATOR;
putenv("HZSVER=v0.1.0-Alfa");

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

$lang_path = $_SESSION["hzSystem_path"]."hzsystem".DIRECTORY_SEPARATOR."lang";
bindtextdomain("hzSystem", $lang_path);

//Second step define SPL autoloader

/**
 * hzSystem SPL autoloader.
 * @param string $classname The name of the class to load
 */
function hzSystemAutoload($classname)
{
    $pathtoclass = str_replace('\\', DIRECTORY_SEPARATOR, $classname);
    $filename = $_SESSION["hzSystem_path"].strtolower($pathtoclass).'.class.php';
    if (is_readable($filename)) {
        require $filename;
    }
}

if (version_compare(PHP_VERSION, '5.1.2', '>=')) {
    //SPL autoloading was introduced in PHP 5.1.2
    if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
        spl_autoload_register('hzSystemAutoload', true, true);
    } else {
        spl_autoload_register('hzSystemAutoload');
    }
} else {
    /**
     * Fall back to traditional autoload for old PHP versions
     * @param string $classname The name of the class to load
     */
    function __autoload($classname)
    {
        hzSystemAutoload($classname);
    }
}
