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

class DB extends DB_Driver { // MySQLi

	var $system;
	var $fieldType;

	function DB($host = 'localhost', $user = 'root', $pwd = '', $dbname = '', $dbprefix = '') {
	    $this->system = 'mysqli';
		$this->errlogfile = 'data/errlog_'.$this->system.'.inc.php';
		parent::DB_Driver($host, $user, $pwd, $dbname, $dbprefix);
		$this->freeResult = false;
		$this->fieldType = array(
			0 => "decimal",
			1 => "tinyint",
			2 => "smallint",
			3 => "integer",
			4 => "float",
			5 => "double",
			7 => "timestamp",
			8 => "bigint",
			9 => "mediumint",
			10 => "date",
			11 => "time",
			12 => "datetime",
			13 => "year",
			14 => "date",
			16 => "bit",
			246 => "decimal",
			247 => "enum",
			248 => "set",
			249 => "tinyblob",
			250 => "mediumblob",
			251 => "longblob",
			252 => "blob",
			253 => "varchar",
			254 => "char",
			255 => "geometry"
		);
	}

	function setPersistence($persistence = false) {
		$this->persistence = false;
	}

	function version () {
		$this->open();
		return @mysqli_get_server_info($this->conn);
	}

	function affected_rows() {
		return mysqli_affected_rows($this->conn);
	}

	function free_result($result = null) {
		if (empty($result)) {
	    	$result = $this->result;
	    }
	    if (is_resource($result)) {
	    	return @mysqli_free_result($result);
	    }
	    else {
	    	return false;
	    }
	}

	function close() {
		if ($this->hasConnection()) {
			if ($this->freeResult == true) {
		    	$this->free_result();
		    }
			return mysqli_close($this->conn);
		}
		else {
			return true;
		}
	}

	function connect($die = true) {
		ob_start();
		$this->conn = mysqli_connect($this->host, $this->user, $this->pwd);
		ob_end_clean();

		$this->quitOnError($die);
	}

	function hasConnection(){
		return is_object($this->conn);
	}

	function isResultSet($result = null){
		if (!is_object($result)) {
	    	$result = $this->result;
	    }
		return is_object($result);
	}

	function select_db($dbname = null) {
		if(empty($dbname)) {
			$dbname = $this->database;
		}
		$this->open();
		return mysqli_select_db($this->conn, $dbname);
	}

	function errno() {
		return mysqli_errno($this->conn);
	}

	function errstr() {
		return mysqli_error($this->conn);
	}

	function query($sql, $line = 0, $file = '', $die = true) {
		$this->open();

		$errfunc = ($die == true) ? E_USER_ERROR : E_USER_NOTICE;
		$zm1 = $this->benchmarktime();

		$this->result = mysqli_query($this->conn, $sql) or trigger_error($this->error($line, $file, $sql), $errfunc);

		$zm2 = $this->benchmarktime();
		$zm=$zm2-$zm1;
		$this->dbqd[] = array('query' => $sql, 'time' => substr($zm,0,7));

	    return $this->result;
	}

	function num_rows($result = null) {
		if (!is_object($result)) {
	    	$result = $this->result;
	    }
	    return @mysqli_num_rows($result);
	}

    function insert_id() {
	    return @mysqli_insert_id($this->conn);
	}

	function data_seek($result = null, $pos = 0) {
		if (!is_object($result)) {
	    	$result = $this->result;
	    }
	    return @mysqli_data_seek($result, $pos);
	}

	function fetch_object($result = null) {
		if (!is_object($result)) {
	    	$result = $this->result;
	    }
	    return @mysqli_fetch_object($result);
	}

	function fetch_num($result = null) {
		if (!is_object($result)) {
	    	$result = $this->result;
	    }
	    return @mysqli_fetch_row($result);
	}

	function fetch_assoc($result = null) {
		if (!is_object($result)) {
	    	$result = $this->result;
	    }
	    return @mysqli_fetch_assoc($result);
	}

	function escape_string($value) {
		$this->open();
		return mysqli_real_escape_string($this->conn, $value);
	}

	function num_fields($result = null) {
		if (!is_object($result)) {
	    	$result = $this->result;
	    }
		return mysqli_num_fields($result);
	}

	function field_len($result = null, $k) {
		if (!is_object($result)) {
	    	$result = $this->result;
	    }
	    $data = mysqli_fetch_field_direct($result, $k);
	    if (!empty($data->length)) {
			return $data->length;
	    }
	    else {
	    	return null;
	    }
	}

	function field_type($result = null, $k) {
		if (!is_object($result)) {
	    	$result = $this->result;
	    }

	    $data = mysqli_fetch_field_direct($result, $k);
	    if ($data != false && isset($this->fieldType[$data])) {
			return $this->fieldType[$data];
	    }
	    else {
	    	return null;
	    }
	}

	function field_name($result = null, $k) {
		if (!is_object($result)) {
	    	$result = $this->result;
	    }
	    $data = mysqli_fetch_field_direct($result, $k);
	    if (!empty($data->orgname)) {
			return $data->orgname;
	    }
	    elseif (!empty($data->name)) {
	    	return $data->name;
	    }
	    else {
	    	return null;
	    }
	}

	function field_table($result = null, $k) {
		if (!is_object($result)) {
	    	$result = $this->result;
	    }
	    $data = mysqli_fetch_field_direct($result, $k);
	    if (!empty($data->orgtable)) {
			return $data->orgtable;
	    }
	    elseif (!empty($data->table)) {
	    	return $data->table;
	    }
	    else {
	    	return null;
	    }
	}

}
?>
