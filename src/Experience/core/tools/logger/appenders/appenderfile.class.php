<?php
    /*
     * appenderfile.class.php
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
     * Lincense
     * -------------------------------------------------------------------------------------------
     * Copyright (C)2025 HZKnight
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
     * -------------------------------------------------------------------------------------------
     */


    namespace Experience\Core\Tools\Logger\Appenders;
        
    use Experience\Core\Tools\Config\EConfigManager;
    use Experience\Core\Tools\Logger\Appenders\Appender;
    use Experience\Core\Tools\Logger\ELogger;
    use Experience\Core\Tools\Logger\ELogRow;
    use Experience\Core\Io\Storage\EStorage;

    use Experience\Core\Exceptions\ENotApplicableMethodException;
    use Experience\Core\Tools\Logger\Exceptions\LogFileNotFoundException;


    /**
     * File appender per ELogger
     *
     * @author  Luca Liscio <lucliscio@h0model.org>
     * @version 0.0.2
     * @copyright 2020-2025 HZKnight
     * @license http://www.gnu.org/licenses/agpl-3.0.html GNU/AGPL3
     *
     * @package eXperience
     * @subpackage Core\Logger\Appenders
     *
     * @filesource
     */
        
    class AppenderFile extends Appender {

        private $logfile;
        private $logfileBaseName;
        private $logfileBaseDir;
        private EStorage $storage;

        /**
         * Construntor method
         *
         * @param String $logname log name
         */
        public function __construct($logname, EConfigManager $cfg, EStorage $storage){
                
            parent::__construct($cfg);
            
            $baseDir = $_SESSION["experience_path"];
            if($this->cfg->has("log_path")){
                $baseDir = $this->cfg->getParam("log_path").DIRECTORY_SEPARATOR;
            }
            
            $this->storage = $storage;
            $this->logfileBaseDir = $baseDir."log";
            $this->logfileBaseName = $logname;
            $this->logfile = $this->logfileBaseDir.DIRECTORY_SEPARATOR.$this->logfileBaseName."_".date("dmY").".log";
        }

        /**
         * Save one row in the log file
         *
         * @param ELogRow $log_row
         */
        public function add(ELogRow $log_row){
            
            $this->createLogDir();
            if($log_row->type >= $this->loglevel){
                $content = "(".$log_row->date.") [".self::$errorIdentifier[$log_row->type]."] --> ".$log_row->message."\n";
                $name = $this->logfile;
                if($this->storage->fileExists($name)){
                    $this->storage->fileWrite($name,$content,"a");
                } else {
                    $this->storage->fileCreate($name,$content);
                }
            }
        }

        /**
         * Return a part of log file
         *
         * @param integer $start start row
         * @param integer $stop end row
         * @return list of log row
         * @throws LogFileNotFoundException
         */
        public function getLog($start,$stop){
            
            if(!$start){
                $start = 0;
            }
            if(!$stop){
                $stop = 0;
            }
                   
            if ($this->storage->fileExists($this->logfile)) {
                $log = $this->storage->fileRead($this->logfile);
                if($stop==null){
                    return array_slice($log, $start);
                } else {
                    return array_slice($log, $start, $stop-$start);
                }
            } else {
                throw new LogFileNotFoundExceprions(dgettext("Elang","Log file not found"));
            }
                                        
        }
           
        /**
         * change the log directory
         *
         * @param string $dir log dirrectory
         */
        public function setLogDir($dir){
            $this->logfileBaseDir = $dir;
            $this->logfile = $this->logfileBaseDir.DIRECTORY_SEPARATOR.$this->logfileBaseName."_".date("dmY").".log";
        }
        
        private function createLogDir(){
            settype($this->logfileBaseDir,"string");
            $__mode=0777;

            if($this->storage->isDir($this->logfileBaseDir)) {
                return;
            } elseif($this->storage->mkdir($this->logfileBaseDir,$__mode)) {
    
                if($this->storage->isDir($this->logfileBaseDir)) {
                    return;
                }
            }

            throw new LogFileNotFoundExceprions("System Error: _mkdir_(".$this->logfileBaseDir.",".$__mode.").");
        }

    }
