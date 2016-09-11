<?php

/* 
 * This is a test file for hzSystem
 */
session_start();

require "../hzSystem_autoloader.php";

use HZSystem\Core\Logger\HZLogger;

echo "<b>hzSystem ".getenv("HZSVER")."</b><br/>";
echo "Lib path: ".$_SESSION["hzSystem_path"]."hzsystem<br/>";
echo "-----<br/>";
echo "Locale: ".getenv("LANG")."<br/>";
echo "Language path: ".$_SESSION["hzSystem_path"]."hzsystem".DIRECTORY_SEPARATOR."lang<br/>";
echo "-----<br/>";
echo "Start logger Test: ";
$log = null;
if($log = HZLogger::getLogger("test")){
    $log->add_appender(HZLogger::LOG_APPENDER_FIREPHP);
    $log->info("Test message");
    echo "OK<br/>";
}
else 
    echo "ERROR<br/>";