<?php

/** 
 * This is a test file for hzSystem
 * 
 * @author  Luca Liscio <hzkight@h0model.org>
 * @version 0.0.1 2016/09/12 19:25:34
 * @copyright 2016 Luca Liscio
 * @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
 *
 * @package hzSystem
 * @subpackage test
 * @filesource
 */

require "../src/hzSystem_autoloader.php";

session_start();

use HZSystem\Core\Logger\HZLogger;

echo "<b>hzSystem ".getenv("HZSVER")."</b><br/><br/>";
echo "Lib path: ".$_SESSION["hzSystem_path"]."hzsystem<br/>";
echo "-----<br/>";
echo "Locale: ".getenv("LANG")."<br/>";
echo "Language path: ".$_SESSION["hzSystem_path"]."hzsystem".DIRECTORY_SEPARATOR."lang<br/>";
echo "-----<br/>";
echo "Start logger Test: ";
$log = null;
if($log = HZLogger::getLogger("test")){
    $log->add_appender(HZLogger::LOG_APPENDER_FIREPHP);
    $log->info("Info Test message");
    $log->error("Error Test message");
    $log->fatal("Fatal Test message");
    $log->debug("Debug Test message");
    $log->warning("Warning Test message");
    echo "OK<br/>";
}
else 
    echo "ERROR<br/>";