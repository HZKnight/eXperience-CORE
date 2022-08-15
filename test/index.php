<?php

/** 
 * This is a test file for Experience
 * 
 * @author  Luca Liscio <lucliscio@h0model.org>
 * @version 0.0.2 2020/11/29 19:25:34
 * @copyright 2021 HZKnight
 * @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
 *
 * @package Experience
 * @subpackage test
 * @filesource
 */

require "../src/experience_autoloader.php";

if(session_id() == ""){
    session_start();
}

use Experience\Core\Logger\ELogger;

echo "<b>Experience CORE ".getenv("ECORE")."</b><br/><br/>";
echo "Verisone PHP richiesta >= 5.2.0 ";
if(version_compare(PHP_VERSION, '5.2.0', '>=')){
    echo "OK hai PHP ".PHP_VERSION."<br/>";
} else {
    echo "ERRORE hai PHP ".PHP_VERSION."<br/>";
}

$_SESSION["script_path"] = __DIR__;

echo "Lib path: ".$_SESSION["experience_path"]."Experience".DIRECTORY_SEPARATOR."<br/>";
echo "-----<br/>";
echo "Locale: ".getenv("LANG")."<br/>";
echo "Script path: ".$_SESSION["script_path"]."<br/>";
echo "Language path: ".$_SESSION["experience_path"]."Experience".DIRECTORY_SEPARATOR."lang<br/>";
echo "Log path: ".$_SESSION["script_path"].DIRECTORY_SEPARATOR."log<br/>";
echo "-----<br/>";
echo "Start logger Test: ";
$log = null;

if($log = ELogger::getLogger("test", ELogger::LOG_APPENDER_FIREPHP)){

    $log->add_appender(ELogger::LOG_APPENDER_FILE);
    $log->get_appender(ELogger::LOG_APPENDER_FILE)->setLogDir($_SESSION["script_path"].DIRECTORY_SEPARATOR."log");
    
    /*
        const EMERGENCY = 407;
        const ALERT     = 406;
        const CRITICAL  = 405;
        const ERROR     = 404;
        const WARNING   = 403;
        const NOTICE    = 402;
        const INFO      = 401;
        const DEBUG     = 400;
    */

    $log->emergency("Emergensy Test message");
    $log->alert("Alert Test message");
    $log->critical("Critical Test message");
    $log->error("Error Test message");
    $log->warning("Warning Test message");
    $log->notice("Notice Test message");   
    $log->info("Info Test message");
    $log->debug("Debug Test message");
    
    echo "OK<br/>";
}
else 
    echo "ERROR<br/>";