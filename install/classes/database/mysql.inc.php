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

include_once(dirname(__FILE__)."/class.db_driver.php");

class DB extends DB_Driver { // MySQL

	var $escaper;
	var $system;

	function DB($host = 'localhost', $user = 'root', $pwd = '', $dbname = '', $dbprefix = '', $open = true) {
	    $this->system = 'mysql';
		if (version_compare(PHP_VERSION, "4.3.0", ">=") && viscacha_function_exists('mysql_real_escape_string') == true) {
			$this->escaper = 'mysql_real_escape_string';
		}
		else {
			$this->escaper = 'mysql_escape_string';
		}
		$this->errlogfile = 'data/errlog_'.$this->system.'.inc.php';
		parent::DB_Driver($host, $user, $pwd, $dbname, $dbprefix, $open);
		$this->freeResult = ($this->persistence == 1);
	}

	function version () {
		$this->open();
		return @mysql_get_server_info();
	}

	function affected_rows() {
		return mysql_affected_rows($this->conn);
	}

	function free_result($result = null) {
		if (!is_resource($result)) {
	    	$result = $this->result;
	    }
	    if (is_resource($result)) {
	    	return @mysql_free_result($result);
	    }
	    else {
	    	return true;
	    }
	}

	function close() {
		if ($this->hasConnection()) {
			if ($this->freeResult == true) {
		    	$this->free_result();
		    }
			return mysql_close($this->conn);
		}
		else {
			return true;
		}
	}

	function connect($die = true) {
		if ($this->persistence == 1) {
			$func = 'mysql_pconnect';
		}
		else {
			$func = 'mysql_connect';
		}

		ob_start();
		$this->conn = $func($this->host, $this->user, $this->pwd);
		ob_end_clean();

		$this->quitOnError($die);
	}

	function hasConnection(){
		return is_resource($this->conn);
	}

	function isResultSet($result = null){
		if (!is_resource($result)) {
	    	$result = $this->result;
	    }
		return is_resource($result);
	}

	function select_db($dbname = null) {
		if(empty($dbname)) {
			$dbname = $this->database;
		}
		$this->open();
		return mysql_select_db($dbname, $this->conn);
	}

	function errno() {
		return mysql_errno();
	}

	function errstr() {
		return mysql_error();
	}

	function query($sql, $line = 0, $file = '', $die = true) {
		$this->open();

		$errfunc = ($die == true) ? E_USER_ERROR : E_USER_NOTICE;
		$zm1 = $this->benchmarktime();

		$this->result = mysql_query($sql, $this->conn) or trigger_error($this->error($line, $file, $sql), $errfunc);

		$zm2 = $this->benchmarktime();
		$zm=$zm2-$zm1;
		$this->dbqd[] = array('query' => $sql, 'time' => substr($zm,0,7));

	    return $this->result;
	}

	function num_rows($result = null) {
		if (!is_resource($result)) {
	    	$result = $this->result;
	    }
	    return @mysql_num_rows($result);
	}
    function insert_id() {
	    return @mysql_insert_id($this->conn);
	}

	function data_seek($result = null, $pos = 0) {
		if (!is_resource($result)) {
	    	$result = $this->result;
	    }
	    return @mysql_data_seek($result, $pos);
	}

	function fetch_object($result = null) {
		if (!is_resource($result)) {
	    	$result = $this->result;
	    }
	    return @mysql_fetch_object($result);
	}

	function fetch_num($result = null) {
		if (!is_resource($result)) {
	    	$result = $this->result;
	    }
	    return @mysql_fetch_row($result);
	}

	function fetch_assoc($result = null) {
		if (!is_resource($result)) {
	    	$result = $this->result;
	    }
	    return @mysql_fetch_assoc($result);
	}

	function escape_string($value) {
		$this->open();
		return call_user_func($this->escaper, $value);
	}

	function num_fields($result = null) {
		if (!is_resource($result)) {
	    	$result = $this->result;
	    }
		return mysql_num_fields($result);
	}

	function field_len($result = null, $k) {
		if (!is_resource($result)) {
	    	$result = $this->result;
	    }
	    $data = mysql_field_len($result, $k);
	    if (!empty($data)) {
			return $data;
	    }
	    else {
	    	return null;
	    }
	}

	function field_type($result = null, $k) {
		if (!is_resource($result)) {
	    	$result = $this->result;
	    }
	    $data = mysql_field_type($result, $k);
	    if (!empty($data)) {
			return $data;
	    }
	    else {
	    	return null;
	    }
	}

	function field_name($result = null, $k) {
		if (!is_resource($result)) {
	    	$result = $this->result;
	    }
	    $data = mysql_field_name($result, $k);
	    if (!empty($data)) {
			return $data;
	    }
	    else {
	    	return null;
	    }
	}

	function field_table($result = null, $k) {
		if (!is_resource($result)) {
	    	$result = $this->result;
	    }
	    $data = mysql_field_table($result, $k);
	    if (!empty($data)) {
			return $data;
	    }
	    else {
	    	return null;
	    }
	}

}
?>
