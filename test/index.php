<?php

    /*
     * index.php
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

    /**
     * This is a test application for Experience
     *
     * @author  Luca Liscio <lucliscio@h0model.org>
     * @version 1.0.0
     * @copyright 2026 HZKnight
     * @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
     *
     * @package Experience
     * @subpackage test
     * @filesource
     */

    require_once "../src/eautoloader.class.php";
    require_once "environment/vendor/RainTpl/rain.tpl.class.php";

    if(session_id() == ""){
        session_start();
    }

    use Experience\Core\Exceptions\EException;
    use Experience\Core\Tools\Logger\ELogger;
    use Experience\Core\Tools\Logger\ELogLevel;
    use Experience\Core\Tools\Config\EConfigManager;
    use Experience\Core\Io\Storage\Estorage;
    use Experience\Core\Io\Storage\Driver\LocalStorageDriver;
    use Experience\Core\Io\Dbal\EDbManager;

    use RainTpl\RainTPL;


    const DEFAULT_ERRROR_MESSAGE = "An error occurred while executing the script.";
 
    //Template manager configuration
    RainTPL::$tpl_ext = "tpl";
    RainTPL::$tpl_dir = __DIR__ . '/assets/templates/';
    RainTPL::$cache_dir = __DIR__ . '/temp/templates_c/';
    RainTPL::$path_replace = false;

    try{

        $view = new RainTPL();

        $view->assign("version", getenv("ECORE"));
        $view->draw("header");

        $view->assign('os', php_uname('s') . " " . php_uname('m'));
        $view->assign('release', php_uname('r'));
        $view->assign('version', php_uname('v'));
        $view->assign('server', $_SERVER['SERVER_SOFTWARE'] ?? 'Esecuzione da riga di comando (CLI)');
        $view->assign('phpversion', PHP_VERSION);
        $view->assign('phpbits', PHP_INT_SIZE * 8);
        $view->assign('phpstatus', version_compare(PHP_VERSION, '7.0.0', '>=') ? 'check' : 'warning');
        $view->assign('phpcolor', version_compare(PHP_VERSION, '7.0.0', '>=') ? 'green' : 'orange');
        $view->assign('experience_version', getenv("ECORE"));
        $view->assign('hostname', php_uname('n'));
        $view->assign('experience_path', $_SESSION["experience_path"]);
        
        $lang = getenv("LANG") ?? 'it_IT';

        $view->assign('locale', $lang);
        $view->assign('script_path', __DIR__);
        $view->assign('language_path', $_SESSION["experience_path"] . "Experience" . DIRECTORY_SEPARATOR . "lang");
        $view->assign('log_path', __DIR__ . DIRECTORY_SEPARATOR . "log");
        

        //Test Storage
        $localstorage = new LocalStorageDriver();
        $estorage = Estorage::getStorage("test_storage", $localstorage);

        $cfg = new EConfigManager('test_config.json', $estorage);

        $view->assign('testCfgRead', 'check');

        $date = new DateTimeImmutable();
        $cfg->setParam("test.time", $date->getTimestamp()." (". $date->format("Y-m-d H:i:s") .")");

        $view->assign('testCfgWrite', 'check');

        $view->assign('cfg', $cfg->getCfg());
        
        $log = null;

        if($log = ELogger::getLogger($cfg, $estorage,  "test", ELogger::LOG_APPENDER_FILE, ELogLevel::DEBUG)) {

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
            
            $view->assign('logger_test', 'check');
            $view->assign('logger_color', 'green');
            $view->assign('mailer_test', 'check');
            $view->assign('mailer_color', 'green');
        }
        else {
            $view->assign('logger_test', 'warning');
            $view->assign('logger_color', 'orange');
            $view->assign('mailer_test', 'warning');
            $view->assign('mailer_color', 'orange');
        }

        $db = new EDbManager($cfg);
        if($db->getError()) {
            $view->assign('db_test', 'warning');
            $view->assign('db_color', 'orange');
        } else {
            $view->assign('db_test', 'check');
            $view->assign('db_color', 'green');

            $result = $db->doUpdate('CREATE TABLE IF NOT EXISTS $_test_table (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255) NOT NULL)');

            //DEfinisco lo stoto di default in caso di errore
            $view->assign('db_conn_test', 'warning');
            $view->assign('db_conn_color', 'orange');
            $view->assign('db_iquery_test', 'warning');
            $view->assign('db_iquery_color', 'orange');
            $view->assign('db_iquery_id', '');
            $view->assign('db_query_test', 'warning');
            $view->assign('db_query_color', 'orange');
            
            if($result && !key_exists("error", $result) ) {

                $view->assign('db_conn_test', 'check');
                $view->assign('db_conn_color', 'green');
                
                $result = $db->doUpdate('INSERT INTO $_test_table (name) VALUES (?)', [1 => 'Test Name']);
                
                if($result && !key_exists("error", $result)) {

                    $view->assign('db_iquery_id', $db->sqlInsertId());
                    $view->assign('db_iquery_test', 'check');
                    $view->assign('db_iquery_color', 'green');
                    
                    $result = $db->doQuery('SELECT * FROM $_test_table WHERE id = ?', [1 => 1]);
                    
                    if($result && !key_exists("error", $result)) {

                        $view->assign('db_query_test', 'check');
                        $view->assign('db_query_color', 'green');

                    } else {

                        $view->assign('db_error', $result ? $result["error"] : DEFAULT_ERRROR_MESSAGE);

                    }
                } else {

                    $view->assign('db_error', $result ? $result["error"] : DEFAULT_ERRROR_MESSAGE);

                }
            } else {

                $view->assign('db_error', $result ? $result["error"] : DEFAULT_ERRROR_MESSAGE);

            }
        }

        $view->draw("body");

        $view->draw("footer");

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
