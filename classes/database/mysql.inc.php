<?php

class DB {
	
	var $host;
	var $user;
	var $pwd;
	var $persist;
	var $open;
	var $dbname;
	var $dbprefix;
	var $system;
	var $escaper;
	var $conn=NULL;
	var $result = FALSE;
	var $dbqd = array();
	var $logerrors = TRUE;
	// Backup
	var $new_line;
	var $commentdel;

	var $errlogfile;

	function DB($host="localhost",$user="root",$pwd="",$dbname="",$persist=0,$open=false, $dbprefix='') {
	    $this->host = $host;
	    $this->user = $user;
	    $this->pwd = $pwd;
	    $this->database = $dbname;
	    $this->persist = $persist;
	    $this->dbprefix = $dbprefix;
	    $this->system = 'mysql';
		$this->errlogfile = 'data/errlog_'.$this->system.'.inc.php';
		if (version_compare(PHP_VERSION, "4.3.0", ">=")) {
			$this->escaper = 'mysql_real_escape_string';
		}
		else {
			$this->escaper = 'mysql_escape_string';
		} 
		if($open) {
		   $this->open();
		}
		define('DB_BOTH', MYSQL_BOTH);
		define('DB_ASSOC', MYSQL_ASSOC);
		define('DB_NUM', MYSQL_NUM);
	}

	function version () {
		return @mysql_get_server_info();
	}
	
    function backup_settings($new_line = "\n", $commentdel = "-- ") {
        $this->new_line = $new_line;
        $this->commentdel = $commentdel;
    }

    function backup($tables, $structure = 1, $data = 1, $drop = 1, $gzip = 0) {
    
	    // Variablen definieren
	    $table_data = $this->commentdel.' Viscacha '.$this->system.'-Backup'.$this->new_line.
				      $this->commentdel.' Host: '.$this->host.$this->new_line.
				      $this->commentdel.' Database: '.$this->database.$this->new_line.
				      $this->commentdel.' Created: '.gmdate('D, d M Y H:i:s').' GMT'.$this->new_line.
				      $this->commentdel.' Tables: '.implode(', ', $tables).$this->new_line;

		// Keine Anfuehrungszeichen in mySQL-Namen
		$this->query('SET SQL_QUOTE_SHOW_CREATE = 0',__LINE__,__FILE__);
	
		// Werte & Struktur der Tabellen ermitteln
		foreach ($tables as $mysql_table) {
		    if ($structure == 1) {
		        if ($drop == 1) {
    		        $table_data .= $this->new_line . $this->new_line. $this->commentdel.' Delete: ' .$mysql_table . $this->new_line;
    		        $table_data .= $this->new_line . 'DROP TABLE IF EXISTS '.chr(96).$mysql_table.chr(96).';' .$this->new_line;
    		    }
    		    $table_data .= $this->new_line. $this->commentdel.' Create: ' .$mysql_table . $this->new_line;
    		    
    		    $result = $this->query('SHOW CREATE TABLE ' .$mysql_table, __LINE__, __FILE__);
    		    $show_results = $this->fetch_array($result, DB_NUM);
    		    if (!$show_results) {
    			    return false;
    		    }
    		    
    		    $table_data .= str_replace("\n", $this->new_line, $show_results[1]). ';' .$this->new_line;
		    }
		    if ($data == 1) {
    		    // Aktuelle Ueberschrift
    		    $table_data .= $this->new_line. $this->commentdel.' Data: ' .$mysql_table . $this->new_line;
    	      
    	     	// Datensaetze vorhanden?
    	     	$result = $this->query('SELECT * FROM ' .$mysql_table,__LINE__,__FILE__);
    	      	if ($this->num_rows($result) > 0) { 
    	      	    while ($select_result = $this->fetch_assoc($result)) {
    	          		// Result-Keys
    	          		$select_result_keys = array_keys($select_result);
    	  	      		foreach ($select_result_keys as $table_field) {
    	  		      		// Struktur & Werte der Tabelle
    	  		      		if (isset($table_structure)) {
    	              		$table_structure .= ', ';
    	              		$table_value .= ', ';
    	  		      		}
    	  		      		else {
    	  			            $table_structure = $table_value = '';
    	  		            }
    	                    $table_structure .= chr(96).$table_field.chr(96);
    	                    $table_value .= "'".$this->escape_string($select_result[$table_field])."'";
    	  		        }
    	  		    
    	  		        // Aktuelle Werte
    	  		        $table_data .= 'INSERT INTO '.chr(96).$mysql_table.chr(96).' (' .$table_structure. ') VALUES (' .$table_value. ');' .$this->new_line;
	  		            // Temporaere Werte annulieren
	  	                unset($table_structure, $table_value);
	  		        }
	  	        }
    	        else {
    	            $table_data .= $this->commentdel.' No entries found!' .$this->new_line;
    	        }
	        } 
        }
	    return $table_data;
    }

	function multi_query($lines) {
		$s = array('queries' => array(), 'ok' => 0);
		$lines = str_replace("\r", "\n", $lines);
		$lines = explode("\n", $lines);
		$lines = array_map("trim", $lines);
		$line = '';
		foreach ($lines as $h) {
			$comment = substr($h, 0, 2);
			if ($comment == '--' || $comment == '//' || strlen($h) <= 10) {
				continue;
			}
			$line .= $h."\n";
		}
		$lines = explode(";\n", $line);
		foreach ($lines as $h) {
			if (strlen($h) > 10) {
				$result = $this->query($h);
				if ($result) {
					if ($this->num_rows($result) > 0) {
						$x = array();
						while ($row = $this->fetch_assoc($result)) {
							$x[] = $row;
						}
						$s['queries'][] = $x;
					}
					$s['ok']++;
				}
			}
		}
		return $s;
	}

	function getcfg($key) {
		switch ($key) {
		case 'host':
		   return $this->host;
		case 'user':
		case 'name':
		   return $this->user;
		case 'password':
		case 'pw':
		   return $this->pwd;
		case 'database':
		case 'db':
		   return $this->database;
		case 'persist':
		   return $this->persist;
		case 'system':
		case 'sql':
		   return $this->system;
		}
	}
	
	function prefix() {
		return $this->dbprefix;
	}

	function benchmark($type='array') {
		if ($type == 'time') {
			$time = 0;
			foreach ($this->dbqd as $query) {
				$time += $query['time'];
			}
			return $time;
		}
		elseif ($type == 'queries') {
			return count($this->dbqd);
		}
		else {
			return $this->dbqd;
		}
	}
	function open($host="",$user="",$pwd="",$dbname="")  {
		if($host!="") {
			$this->host=$host;
		}
	 	if($user!="") {
	    	$this->user=$user;
	    }
	 	if($pwd!="") {
	    	$this->pwd=$pwd;
	    }
	 	if($dbname!="") {
	    	$this->database=$dbname;
	    }
		$this->connect();
		$this->select_db();
	}
	
	function affected_rows() {
		return mysql_affected_rows($this->conn);
	}
	function free_result($result = '') {
		if ($result == '') {
	    	$result = $this->result;
	    }
	    return (@mysql_free_result($result));
	}
	function close() {
		if (!empty($this->result)) {
	    	$this->free_result();
	    }
		return mysql_close($this->conn);
	}
	function connect($die = true) {
		if ($this->persist == 1) {
			$func = 'mysql_pconnect';
		}
		else {
			$func = 'mysql_connect';
		}

		$this->conn = @$func($this->host, $this->user, $this->pwd);
		if(!$this->conn && $die == true) {
			die('Could not connect to database! Pleasy try again later or check the database settings: host, username and password!');
		}
	}
	function select_db($dbname="") {
		if($dbname=="") {
			$dbname=$this->database;
		}
		return mysql_select_db($dbname,$this->conn);
	}
	
	function error($errline, $errfile, $errcomment) {
		if ($this->logerrors) {
			$new = array();
			if (file_exists($this->errlogfile)) {
				$lines = file($this->errlogfile);
				foreach ($lines as $row) {
					$row = trim($row);
					if (!empty($row)) {
						$new[] = $row;
					}
				}
			}
			else {
				$new = array();
			}
			$errno = mysql_errno();
			$errmsg = mysql_error();
			$errcomment = str_replace("\n", " ", $errcomment);
			$errcomment = str_replace("\r", " ", $errcomment);
			$errcomment = str_replace("\t", "   ", $errcomment);
			$errmsg = str_replace("\n", " ", $errmsg);
			$errmsg = str_replace("\r", " ", $errmsg);
			$errmsg = str_replace("\t", "   ", $errmsg);
			$_SERVER['REQUEST_URI'] = str_replace("\n", " ", $_SERVER['REQUEST_URI']);
			$_SERVER['REQUEST_URI'] = str_replace("\r", " ", $_SERVER['REQUEST_URI']);
			$_SERVER['REQUEST_URI'] = str_replace("\t", "   ", $_SERVER['REQUEST_URI']);
			$new[] = $errno."\t".$errmsg."\t".$errfile."\t".$errline."\t".$errcomment."\t".$_SERVER['REQUEST_URI']."\t".time()."\t".PHP_VERSION." (".PHP_OS.")";
			file_put_contents($this->errlogfile, implode("\n", $new));
		}
	    return "$errno: $errmsg - File: $errfile on line $errline";
	}
	function benchmarktime() {
	   list($usec, $sec) = explode(" ", microtime());
	   return ((float)$usec + (float)$sec);
	}
	function query($sql, $line = 0, $file = '', $unbuffered = FALSE) {
		global $config;

		$zm1 = $this->benchmarktime();

		if ($unbuffered == TRUE) {
			$func = 'mysql_unbuffered_query';
		}
		else {
			$func = 'mysql_query';
		}

		$this->result = $func($sql, $this->conn) or die('Database error: '.$this->error($line, $file, $sql));
	    
		$zm2 = $this->benchmarktime();
		
		$zm=$zm2-$zm1;
		
		$this->dbqd[] = array('query' => $sql, 'time' => substr($zm,0,7));
	    
	    return $this->result;
	}
	function num_rows($result='') {
		if ($result == '') {
	    	$result = $this->result;
	    }
	    return (@mysql_num_rows($result));
	}
    function insert_id() {
	    return (@mysql_insert_id($this->conn));
	}
	function fetch_object($result='') {
		if ($result == '') {
	    	$result = $this->result;
	    }
	    return (@mysql_fetch_object($result));
	}
	function fetch_array($result='',$prefix=DB_BOTH) {
		if ($result == '') {
	    	$result = $this->result;
	    }
	    return (@mysql_fetch_array($result, $prefix));
	}
	function fetch_assoc($result='') {
		if ($result == '') {
	    	$result = $this->result;
	    }
	    return (@mysql_fetch_assoc($result));
	}
	function escape_string($value) {
		$func = $this->escaper;
		return $func($value);
	}
	function list_tables($result='') {
		if ($result == '') {
			$result = $this->database;
		}
		$result = $this->query('SHOW TABLES FROM '.$result,__LINE__,__FILE__);
		$tables = array();
		while ($row = $this->fetch_array($result)) {
			$tables[] = $row[0];
		}
		return $tables;
	}
	function num_fields($result='') {
		if ($result == '') {
	    	$result = $this->result;
	    }
		return mysql_num_fields($result);
	}
	function field_flags($result='') {
		if ($result == '') {
	    	$result = $this->result;
	    }
		return mysql_field_flags($result, $k);
	}
	function field_len($result='',$k) {
		if ($result == '') {
	    	$result = $this->result;
	    }
		return mysql_field_len($result, $k);
	}
	function field_type($result='',$k) {
		if ($result == '') {
	    	$result = $this->result;
	    }
		return mysql_field_type($result, $k);
	}
	function field_name($result='',$k) {
		if ($result == '') {
	    	$result = $this->result;
	    }
		return mysql_field_name($result, $k);
	}
	function field_table($result='',$k) {
		if ($result == '') {
	    	$result = $this->result;
	    }
		return mysql_field_table($result, $k);
	}

}
?>
