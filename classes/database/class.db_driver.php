<?php
/*
	Viscacha - A bulletin board solution for easily managing your content
	Copyright (C) 2004-2007  Matthias Mohr, MaMo Net

	Author: Matthias Mohr
	Publisher: http://www.viscacha.org
	Start Date: May 22, 2004

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

class DB_Driver {

	var $host;
	var $user;
	var $pwd;
	var $open;
	var $dbname;
	var $pre;
	var $conn;
	var $result;
	var $dbqd;
	var $logerrors;
	var $freeResult;
	var $new_line;
	var $commentdel;
	var $errlogfile;

	function DB_Driver($host="localhost", $user="root", $pwd="", $dbname="", $dbprefix='', $open=false) {
	    $this->host = $host;
	    $this->user = $user;
	    $this->pwd = $pwd;
	    $this->database = $dbname;
	    $this->pre = $dbprefix;
	    $this->freeResult = true;
	    $this->result = false;
	    $this->conn = null;
	    $this->logerrors = true;
	    $this->dbqd = array();
		if($open) {
		   $this->open();
		}
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
		$this->query('SET SQL_QUOTE_SHOW_CREATE = 1',__LINE__,__FILE__);

		// Werte & Struktur der Tabellen ermitteln
		foreach ($tables as $mysql_table) {
		    if ($structure == 1) {
		        if ($drop == 1) {
    		        $table_data .= $this->new_line . $this->new_line. $this->commentdel.' Delete: ' .$mysql_table . $this->new_line;
    		        $table_data .= $this->new_line . 'DROP TABLE IF EXISTS '.chr(96).$mysql_table.chr(96).';' .$this->new_line;
    		    }
    		    $table_data .= $this->new_line. $this->commentdel.' Create: ' .$mysql_table . $this->new_line;

    		    $result = $this->query('SHOW CREATE TABLE ' .$mysql_table, __LINE__, __FILE__);
    		    $show_results = $this->fetch_num($result);
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

	function multi_query($lines, $die = true) {
		$s = array('queries' => array(), 'ok' => 0, 'affected' => 0);
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
				unset($result);
				$result = $this->query($h, __LINE__, __FILE__, $die);
				if ($this->isResultSet($result)) {
					if ($this->num_rows($result) > 0) {
						$x = array();
						while ($row = $this->fetch_assoc($result)) {
							$x[] = $row;
						}
						$s['queries'][] = $x;
					}
				}
				if ($result == true) {
					$s['affected'] = $this->affected_rows();
				}
				if ($result) {
					$s['ok']++;
				}
			}
		}
		return $s;
	}

	function prefix() {
		return $this->pre;
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
		if(!empty($host)) {
			$this->host=$host;
		}
	 	if(!empty($user)) {
	    	$this->user=$user;
	    }
	 	if(!empty($pwd)) {
	    	$this->pwd=$pwd;
	    }
	 	if(!empty($dbname)) {
	    	$this->database=$dbname;
	    }
		$this->connect();
		$this->select_db();
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
			$errno = $this->errno();
			$errmsg = $this->errstr();
			$errcomment = str_replace(array("\r\n","\n","\r","\t"), " ", $errcomment);
			$errmsg = str_replace(array("\r\n","\n","\r","\t"), " ", $errmsg);
			$sru = str_replace(array("\r\n","\n","\r","\t"), " ", $_SERVER['REQUEST_URI']);
			$new[] = $errno."\t".$errmsg."\t".$errfile."\t".$errline."\t".$errcomment."\t".$sru."\t".time()."\t".PHP_VERSION." (".PHP_OS.")";
			@file_put_contents($this->errlogfile, implode("\n", $new));
		}
		$errcomment = nl2br($errcomment);
	    return "Database error {$errno}: {$errmsg}<br />File: {$errfile} on line {$errline}<br />Query: <code>{$errcomment}</code>";
	}

	function benchmarktime() {
	   list($usec, $sec) = explode(" ", microtime());
	   return ((float)$usec + (float)$sec);
	}

	function list_tables($db = null) {
		if ($db == null) {
			$db = $this->database;
		}
		$result = $this->query('SHOW TABLES FROM `'.$db.'`',__LINE__,__FILE__);
		$tables = array();
		while ($row = $this->fetch_num($result)) {
			$tables[] = $row[0];
		}
		return $tables;
	}

	function list_fields($table) {
		$result = $this->query('SHOW COLUMNS FROM '.$table,__LINE__,__FILE__);
		$columns = array();
		while ($row = $this->fetch_num($result)) {
			$columns[] = $row[0];
		}
		return $columns;
	}

}
?>
