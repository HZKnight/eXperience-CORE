<?php

/** 
 * This is a test file for hzSystem
 * 
 * @author  Luca Liscio <hzkight@h0model.org>
 * @version 0.0.1 2016/09/12 19:25:34
 * @copyright 2020 HZKnight
 * @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
 *
 * @package hzSystem
 * @subpackage test
 * @filesource
 */

require "../src/experience_autoloader.php";

session_start();

use Experience\Core\Logger\HZLogger;

echo "<b>hzSystem ".getenv("HZSVER")."</b><br/><br/>";
echo "Verisone PHP richiesta >= 5.1.2 ";
if(version_compare(PHP_VERSION, '5.1.2', '>=')){
    echo "OK hai PHP ".PHP_VERSION."<br/>";
} else {
    echo "ERRORE hai PHP ".PHP_VERSION."<br/>";
}

$_SESSION["script_path"] = __DIR__;

echo "Lib path: ".$_SESSION["hzSystem_path"]."hzsystem".DIRECTORY_SEPARATOR."<br/>";
echo "-----<br/>";
echo "Locale: ".getenv("LANG")."<br/>";
echo "Script path: ".$_SESSION["script_path"]."<br/>";
echo "Language path: ".$_SESSION["hzSystem_path"]."hzsystem".DIRECTORY_SEPARATOR."lang<br/>";
echo "Log path: ".$_SESSION["script_path"].DIRECTORY_SEPARATOR."log<br/>";
echo "-----<br/>";
echo "Start logger Test: ";
$log = null;

if($log = HZLogger::getLogger("test", HZLogger::LOG_APPENDER_FIREPHP)){

    $log->add_appender(HZLogger::LOG_APPENDER_FILE);
    $log->get_appender(HZLogger::LOG_APPENDER_FILE)->setLogDir($_SESSION["script_path"].DIRECTORY_SEPARATOR."log");

    $log->info("Info Test message");
    $log->error("Error Test message");
    $log->fatal("Fatal Test message");
    $log->debug("Debug Test message");
    $log->warning("Warning Test message");
    echo "OK<br/>";
}
else 
    echo "ERROR<br/>";