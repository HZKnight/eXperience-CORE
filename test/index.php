<html>
    <head>
        <title>eXperience CORE Test</title>
        <!-- UIkit CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/uikit@3.25.13/dist/css/uikit.min.css" />
        <!-- UIkit Icons -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/uikit@3.25.13/dist/css/uikit-icons.min.css" />


        <!-- UIkit JS -->
        <script src="https://cdn.jsdelivr.net/npm/uikit@3.25.13/dist/js/uikit.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/uikit@3.25.13/dist/js/uikit-icons.min.js"></script>
    </head>
    <body>
    <nav class="uk-navbar-container">
        <div class="uk-container">
            <div uk-navbar>

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

require "../src/eautoloader.class.php";

if(session_id() == ""){
    session_start();
}

use Experience\Core\Exceptions\EException;
use Experience\Core\Tools\Logger\ELogger;
use Experience\Core\Tools\Logger\ELogLevel;
use Experience\Core\Tools\Config\EConfigManager;
use Experience\Core\Io\Storage\Estorage;
use Experience\Core\Io\Storage\Driver\LocalStorageDriver;


echo "<h1 class='uk-navbar-item uk-logo'>eXperience CORE ".getenv("ECORE")." - Test application</h1>";
echo "</div>";
echo "</div>";
echo "</nav>";
echo "<p>Questo è un file di test per eXperience CORE, non è destinato ad essere eseguito in produzione.</p><hr>";
echo "<p>Il test include:</p><ul>
<li>Informazioni di sistema</li>
<li>Test del logger</li>
<li>Test del gestore di configurazione</li>
</ul><hr>";

try{
    //Show system information
    echo "<h2 class='uk-heading-divider'>System information</h2>";
    echo "<b>Sistema Operativo</b>: " . php_uname('s') . " " . php_uname('m') . "<br/>";
    echo "<b>Release</b>: " . php_uname('r') . "<br/>";
    echo "<b>Versione</b>: " . php_uname('v') . "<br/>";    

    $server = $_SERVER['SERVER_SOFTWARE'] ?? 'Esecuzione da riga di comando (CLI)';
    echo "<b>Server Web</b>: " . $server . "<br/>";

    echo "<b>Versione PHP</b>: ".PHP_VERSION." (".(PHP_INT_SIZE * 8)." bit) ";
     if(version_compare(PHP_VERSION, '7.0.0', '>=')){
        echo "<b><span uk-icon='icon: check; ratio: 1' style='color: green;'></span></b><br/>";
    } else {
        echo "<b><span uk-icon='icon: warning; ratio: 1' style='color: orange;'></span></b><br/>";
    }
    
    echo "<b>Versione eXperience CORE</b>: ".getenv("ECORE")."<br/>";
    echo "<b>Nome Host</b>: " . php_uname('n') . "<br/>";


    //Test Storage
    $localstorage = new LocalStorageDriver();
    $estorage = Estorage::getStorage("test_storage", $localstorage);

    $cfg = new EConfigManager('test_config.json', $estorage);

    $_SESSION["script_path"] = __DIR__;

    echo "Lib path: ".$_SESSION["experience_path"]."Experience".DIRECTORY_SEPARATOR;
    echo "-<hr>";
    echo "Locale: ".getenv("LANG")."<br/>";
    echo "Script path: ".$_SESSION["script_path"]."<br/>";
    echo "Language path: ".$_SESSION["experience_path"]."Experience".DIRECTORY_SEPARATOR."lang<br/>";
    echo "Log path: ".$_SESSION["script_path"].DIRECTORY_SEPARATOR."log<br/>";
    echo "Configurazione: <pre>";
    var_dump($cfg);
    echo "</pre>";
    echo "-<hr>";
    echo "Start logger Test: ";
    $log = null;

    if($log = ELogger::getLogger($cfg, $estorage,  "test", ELogger::LOG_APPENDER_FILE, ELogLevel::INFO)){

        $log->get_appender(ELogger::LOG_APPENDER_FILE)->setLogDir("log");
        

        //Add email appender
        $log->add_appender(ELogger::LOG_APPENDER_EMAIL);

        //Simulate error message
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
    
    $date = new DateTimeImmutable();
    $cfg->setParam("test.time", $date->getTimestamp());
} catch(Throwable $e){
    if($e instanceof EException){
        $code = $e->getInternalCode();
        $name = $e->getName();
    } else {
        $code = $e->getCode();
        $name = get_class($e);
    }
    echo "<div class=\"uk-alert-danger\" uk-alert>";
    echo "<h3 class='uk-heading-divider'><span uk-icon='icon: warning; ratio: 2'></span> ERRORE: ".$code." - ". $name ."</h3>";
    echo "<div style='color: black; padding: 10px; border-radius: 5px;'>";
    echo "<b>Messaggio</b>: ".$e->getMessage()."<br><b>File</b>: ". $e->getFile() ." <b>(". $e->getLine().")</b><br/>";
    echo "</div>";
    $trace = $e->getTrace();


    echo "<h4 class='uk-heading-divider'>Stack trace:</h4>";
    echo "<table class='uk-table uk-table-divider uk-table-small uk-table-hover uk-table-responsive'>";
    echo "<tr'>
            <th>#</th>
            <th>File (Linea)</th>
            <th>Chiamata (Funzione/Metodo)</th>
        </tr>";

    foreach ($trace as $index => $step) {
        // Gestiamo i dati mancanti con valori di default
        $file = $step['file'] ?? 'N/A';
        $line = $step['line'] ?? 'N/A';
        $class = $step['class'] ?? '';
        $type = $step['type'] ?? ''; // -> o ::
        $function = $step['function'];

        echo "<tr>";
        echo "<td>$index</td>";
        echo "<td>$file <b>($line)</b></td>";
        echo "<td>$class$type$function()</td>";
        echo "</tr>";
    }

    echo "</div>";
}
?>
    </body>
</html>