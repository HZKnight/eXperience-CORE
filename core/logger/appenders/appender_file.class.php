<?php

	namespace HZSystem\Core\Logger\Appenders;
	
	/**
	 * @author luca
	 *
	 */
	class Appender_file extends Appender {
		
		private $logfile;
		private $logfile_basename;
		private $logfile_basedir;
		
		public function __construct($logname){
			$this->logfile_basedir = "log";
			$this->logfile_basename = $logname;
			$this->logfile = $this->logfile_basedir."/".$this->logfile_basename."_".date("dmY").".log";
		}
		
		public function add($log_row){
			error_log(date($this->date_format).' --> '.$msg."\n",3,$this->logfile);
		}
		
		public function get_log($start=0,$stop){
			
		}
		
	}