<?php
/*
	Viscacha - A bulletin board solution for easily managing your content
	Copyright (C) 2004-2009  The Viscacha Project

	Author: Matthias Mohr (et al.)
	Publisher: The Viscacha Project, http://www.viscacha.org
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

abstract class DB_Driver {

	var $host;
	var $user;
	var $pwd;
	var $open;
	var $database;
	var $pre;
	var $conn;
	var $result;
	var $new_line;
	var $commentdel;
	var $std_limit;
	var $system;

	function __construct($host="localhost", $user="root", $pwd="", $dbname="", $dbprefix='') {
	    $this->host = $host;
	    $this->user = $user;
	    $this->pwd = $pwd;
	    $this->database = $dbname;
	    $this->pre = $dbprefix;
	    $this->result = false;
	    $this->conn = null;
        $this->new_line = "\n";
        $this->commentdel = '-- ';
        $this->std_limit = 5000;
	}

	function quitOnError($die = true) {
		if (!$this->hasConnection()) {
			if ($die == true) {
				trigger_error('Could not connect to database! Pleasy try again later or check the database settings: host, username and password!<br /><strong>Database returned</strong>: '.$this->errstr(), E_USER_ERROR);
			}
			else {
				trigger_error('Could not connect to database!<br /><strong>Database returned</strong>: '.$this->errstr(), E_USER_WARNING);
			}
		}
	}

    function getStructure($table, $drop = 1) {
    	// Activate Quotes in sql names
    	$this->query('SET SQL_QUOTE_SHOW_CREATE = 1');

    	$table_data = '';
        if ($drop == 1) {
	        $table_data .= $this->new_line . $this->new_line. $this->commentdel.' Delete: ' .$table . $this->new_line;
	        $table_data .= 'DROP TABLE IF EXISTS '.chr(96).$table.chr(96).';' .$this->new_line;
	    }
	    $table_data .= $this->new_line. $this->commentdel.' Create: ' .$table . $this->new_line;

	    $result = $this->query('SHOW CREATE TABLE '.chr(96).$table.chr(96));
	    $show_results = $this->fetch_num($result);
	    if (!$show_results) {
		    return false;
	    }

	    $table_data .= str_replace(array("\r\n", "\r", "\n"), $this->new_line, $show_results[1]). ';' .$this->new_line;
	    return trim($table_data);
    }

    // offset = -1 => Alle Zeilen
    // offset >= 0 => Ab offset die nächsten $this->std_limit Zeilen
    function getData($table, $offset = -1) {
	    $table_data = $this->new_line. $this->commentdel.' Data: ' .$table . iif ($offset != -1, ' {'.$offset.', '.($offset+$this->std_limit).'}' ). $this->new_line;
     	// Datensaetze vorhanden?
     	$result = $this->query('SELECT * FROM '.chr(96).$table.chr(96).iif($offset >= 0, " LIMIT {$offset},{$this->std_limit}"));
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
	        $table_data .= 'INSERT INTO '.chr(96).$table.chr(96).' (' .$table_structure. ') VALUES (' .$table_value. ');' .$this->new_line;
			unset($table_structure, $table_value);
  	    }
		return trim($table_data);
    }

	function multi_query($lines, $die = true) {
		$s = array('queries' => array(), 'ok' => 0, 'affected' => 0);
		$lines = str_replace("\r", "\n", $lines);
		$lines = explode("\n", $lines);
		$lines = array_map("trim", $lines);
		$line = '';
		foreach ($lines as $h) {
			$comment = mb_substr($h, 0, 2);
			if ($comment == '--' || $comment == '//' || empty($h)) {
				continue;
			}
			$line .= $h."\n";
		}
		$lines = array_map('trim', explode(";\n", $line));
		foreach ($lines as $h) {
			if (!empty($h)) {
				unset($result);
				$result = $this->query($h, $die);
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

	function open($host=null,$user=null,$pwd=null,$dbname=null)  {
		if (!$this->hasConnection()) {
			if($host != null) {
				$this->host = $host;
			}
		 	if($user != null) {
		    	$this->user = $user;
		    }
		 	if($pwd != null) {
		    	$this->pwd = $pwd;
		    }
		 	if($dbname != null) {
		    	$this->database = $dbname;
		    }
			$this->connect();
			$this->select_db();
		}
	}

	function error($errcomment) {
		// Try to get better results for line and file.
		if (function_exists('debug_backtrace') == true) {
			$backtraceInfo = debug_backtrace();
			// 0 is class.mysqli.php, 1 is the calling code...
			if (isset($backtraceInfo[1]) == true) {
				$errline = $backtraceInfo[1]['line'];
				$errfile = $backtraceInfo[1]['file'];
			}
		}

		$cols = array(
			$this->errno(),
			makeOneLine($this->errstr()),
			$errfile,
			$errline,
			makeOneLine($_SERVER['REQUEST_URI']),
			time(),
			makeOneLine($errcomment)
		);
		$lines[] = implode("\t", $cols);
		Debug::error($lines);

		$errcomment = nl2br($errcomment);
	    return "DB ERROR ".$this->errno().": ".$this->errstr()."<br />File: {$errfile} on line {$errline}<br />Query: <code>{$errcomment}</code>";
	}

	function list_tables($db = null) {
		if ($db == null) {
			$db = $this->database;
		}
		$result = $this->query('SHOW TABLES FROM `'.$db.'`');
		$tables = array();
		while ($row = $this->fetch_num($result)) {
			$tables[] = $row[0];
		}
		return $tables;
	}

	function list_fields($table) {
		$result = $this->query('SHOW COLUMNS FROM '.$table);
		$columns = array();
		while ($row = $this->fetch_assoc($result)) {
			$columns[] = $row['Field'];
		}
		return $columns;
	}

	function fetch_one($result = null) {
	    $row = $this->fetch_num($result);
		return isset($row[0]) ? $row[0] : null;
	}

}