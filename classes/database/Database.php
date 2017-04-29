<?php

/*
  Viscacha - An advanced bulletin board solution to manage your content easily
  Copyright (C) 2004-2017, Lutana
  http://www.viscacha.org

  Authors: Matthias Mohr et al.
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

namespace Viscacha\Database;

class Database {

	private static $instance = null;
	protected $system;
	protected $host;
	protected $user;
	protected $password;
	protected $database;
	protected $charset;
	protected $collate;
	public $pre; // TODO: Make protected
	protected $connection;
	protected $lastStatament;

	/**
	 * Initializes a connection
	 * 
	 * @param string $system Currently only "mysql" supported
	 * @param string $user Database user
	 * @param string $password Database password
	 * @param string $host Database host
	 * @param string $database Name of the database to use
	 * @param string $prefix Prefix for tables, e.g. "v_"
	 * @throws PDOException
	 */
	function __construct($system, $user, $password, $host = "localhost", $database = null, $prefix = 'v_', $charset = '', $collate = '') {
		$this->lastStatament = null;
		$this->system = $system;
		$this->host = $host;
		$this->user = $user;
		$this->password = $password;
		$this->database = $database;
		$this->pre = $prefix;
		$this->charset = $charset;
		$this->collate = $collate;
		$this->connect();
	}

	public static function getInstance() {
		return self::$instance;
	}
	
	protected function connect() {
		$settings = array(
			\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
			\PDO::ATTR_PERSISTENT => false
		);
		if ($this->system == 'mysql') {
			$settings[\PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES {$this->charset} COLLATE {$this->collate};";
		} // ;charset={$this->charset}
		$this->connection = new \PDO("{$this->system}:host={$this->host};dbname={$this->database}", $this->user, $this->password, $settings);
	}

	public function makeGlobal($alias = null) {
		self::$instance = $this;
		if ($alias !== null) {
			class_alias(get_class($this), $alias);
		}
	}
	
	public function builder() {
		if ($this->system == 'mysql') {
			return new MysqlQuery($this);
		}
		else {
			throw new NotImplementedException();
		}
	}

	public function getPdoObject() {
		return $this->pdo;
	}

	public function getSystem() {
		return $this->system;
	}

	public function getHost() {
		return $this->host;
	}

	public function getUser() {
		return $this->user;
	}

	public function getPassword() {
		return $this->password;
	}

	public function getDatabase() {
		return $this->database;
	}

	public function getPrefix() {
		return $this->pre;
	}

	public function getCharset() {
		return $this->charset;
	}

	public function getCollate() {
		return $this->collate;
	}

	public function transaction() {
		return $this->connection->beginTransaction();
	}
	
	public function commit() {
		return $this->connection->commit();
	}

	function rollback() {
		return $this->connection->rollBack();
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
		$line = implode("\t", $cols);
		\Debug::error($line);

		$errcomment = nl2br($errcomment);
		return "DB ERROR " . $this->errno() . ": " . $this->errstr() . "<br />File: {$errfile} on line {$errline}<br />Query: <code>{$errcomment}</code>";
	}

	function list_tables() {
		$result = $this->builder()->listTables()->execute();
		// ToDo: FetchColumn
		$tables = array();
		while ($row = $result->fetch_num()) {
			$tables[] = $row[0];
		}
		return $tables;
	}

	function list_fields($table) {
		$result = $this->builder()->listColumns()->execute();
		$columns = array();
		while ($row = $this->fetch_assoc($result)) {
			$columns[] = $row['Field'];
		}
		return $columns;
	}

	function version() {
		return $this->connection->getAttribute(\PDO::ATTR_SERVER_VERSION);
	}

	function errno() {
		return $this->connection->errorCode();
	}

	function errstr() {
		$error = $this->connection->errorInfo();
		return $error[2];
	}

	function query($sql, $values = array()) {
		\Debug::startMeasurement("Database::query()");
		$stmt = $this->connection->prepare($sql);
		$stmt->execute($values);
		\Debug::stopMeasurement("Database::query()", array('query' => $sql, 'type' => 'db'));
		
		if ($stmt instanceof \PDOStatement) {
			$stmt = new Result($stmt);
		}
		$this->lastStatament = $stmt;

		return $stmt;
	}

	function multi_query($queries) { // ToDo: Implement
		throw new NotImplementedException();
/*		$s = array('queries' => array(), 'ok' => 0, 'affected' => 0);
		$lines = preg_split("\r", "\n", $lines, PREG_SPLIT_NO_EMPTY);
		$lines = explode("\n", $lines);
		$lines = array_map("trim", $lines);
		$line = '';
		foreach ($lines as $h) {
			$comment = mb_substr($h, 0, 2);
			if ($comment == '--' || $comment == '//' || empty($h)) {
				continue;
			}
			$line .= $h . "\n";
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
					$s['ok'] ++;
				}
			}
		}
		return $s; */
	}

	function insert_id() {
		return $this->connection->lastInsertId();
	}

	function escape_string($value) {
		return trim($this->connection->quote($value), "'"); // Remove the trim stuff
	}
	
	public function __call($name, $arguments) {
		$statement = $this->lastStatament;
		if (isset($arguments[0]) && $arguments[0] instanceof Result) {
			$statement = $arguments[0];
		}
		if (!($statement instanceof Result)) {
			trigger_error("Invalid result specified", E_USER_NOTICE);
		}
		$callable = array($statement, $name);
		if (is_callable($callable)) {
			return call_user_func_array($callable, $arguments);
		}
		
		trigger_error("Inaccessible method '{$name}'", E_USER_NOTICE);
	}
}

class Result {

	private $statement;
	
	public function __construct(\PDOStatement $pdoStatement) {
		$this->statement = $pdoStatement;
	}

	public function num_rows() {
		return $this->affected_rows(); // ToDo: Remove from code
	}

	public function num_fields() {
		return $this->statement->columnCount();
	}

	public function fetch_all_assoc() {
		return $this->statement->fetchAll(\PDO::FETCH_ASSOC);
	}

	public function fetch_all_object() {
		return $this->statement->fetchAll(\PDO::FETCH_OBJ);
	}

	public function fetch_assoc() {
		return $this->statement->fetch(\PDO::FETCH_ASSOC);
	}

	public function fetch_object() {
		return $this->statement->fetch(\PDO::FETCH_OBJ);
	}

	public function fetch_one($column = 1) {
		if (is_string($column)) {
			$row = $this->fetch_assoc();
		}
		else {
			$row = $this->fetch_num();
			$column--;
		}
		return isset($row[$column]) ? $row[$column] : null;
	}

	public function fetch_column($column = 1) {
		$row = $this->fetch_num($result);
		return isset($row[$column - 1]) ? $row[$column - 1] : null;
	}

	public function affected_rows() {
		return $this->statement->rowCount();
	}

}