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

	protected $system;
	protected $host;
	protected $user;
	protected $password;
	protected $database;
	protected $charset;
	protected $collate;
	public $pre; // TODO: Make protected
	protected $connection;

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
	
	protected function connect() {
		$settings = array(
			\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
			\PDO::ATTR_PERSISTENT => false
		);
		if ($this->system == 'mysql') {
			$settings[\PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES {$this->charset} COLLATE {$this->collate};";
			$settings[\PDO::MYSQL_ATTR_MULTI_STATEMENTS] = false;
		} 
		$this->connection = new \PDO("{$this->system}:host={$this->host};dbname={$this->database};charset={$this->charset}", $this->user, $this->password, $settings);
	}

	public function populateGlobal($alias = null) {
		DB::setInstance($this);
		if ($alias !== null) {
			class_alias(DB::class, $alias);
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

	public function rollback() {
		return $this->connection->rollBack();
	}

	public function getTables() {
		return $this->builder()->listTables()->fetchList();
	}

	public function getColumns($table) {
		$result = $this->builder()->listColumns()->fetchList('Field'); // ToDo: Field is proprietary for MySQL => generalize
	}

	public function getVersion() {
		return $this->connection->getAttribute(\PDO::ATTR_SERVER_VERSION);
	}

	public function getErrorNumber() {
		return $this->connection->errorCode();
	}

	public function getErrorMessage() {
		$error = $this->connection->errorInfo();
		return $error[2];
	}
	
	public function fetch($sql, $values = array()) {
		$stmt = $this->execute($sql, $values);
		return $stmt->fetch();
	}
	
	public function fetchObject($sql, $values = array(), $class = null) {
		$stmt = $this->execute($sql, $values);
		return $stmt->fetchObject($class);
	}
	
	public function fetchOne($sql, $values = array(), $column = 1) {
		$stmt = $this->execute($sql, $values);
		if ($stmt) {
			return $stmt->fetchOne($column);
		}
		else {
			return false;
		}
	}
	
	public function fetchMatrix($sql, $values = array()) {
		$stmt = $this->execute($sql, $values);
		return $stmt->fetchMatrix();
	}
	
	public function fetchObjectMatrix($sql, $values = array(), $class = null) {
		$stmt = $this->execute($sql, $values);
		return $stmt->fetchObject($class);
	}
	
	public function fetchList($sql, $values = array(), $column = 1) {
		$stmt = $this->execute($sql, $values);
		return $stmt->fetchList($column);
	}

	public function execute($sql, $values = array()) {
		\Debug::startMeasurement("Database::query()");
		try {
			$stmt = $this->connection->prepare($sql);
			$stmt->execute($values);
		} catch(PDOException $e) {
			throw $e;
		} finally {
			\Debug::stopMeasurement("Database::query()", array('query' => $sql, 'type' => 'db'));
		}
		
		if ($stmt instanceof \PDOStatement) {
			return new Result($stmt, $sql, $values);
		}
		return $stmt;
	}

	public function executeMultiple($sql) { // ToDo: Implement
		$statements = array();
		$lines = preg_split('~\s*(\r\n|\r|\n)\s*~u', $sql, -1, PREG_SPLIT_NO_EMPTY);
		$sqlWithoutComments = '';
		foreach ($lines as $h) {
			$comment = mb_substr($h, 0, 2);
			// ToDo: Comment chars are for MySQL => generalize
			if ($comment == '--' || $comment == '//' || empty($h)) {
				continue;
			}
			$sqlWithoutComments .= $h . PHP_EOL;
		}
		// ToDo: Query separation char (;) might be MySQL only => generalize
		$queries = preg_split('~\s*;\s*(\r\n|\r|\n)\s*~u', $sqlWithoutComments, -1, PREG_SPLIT_NO_EMPTY);
		foreach ($queries as $query) {
			$stmt = $this->execute($query);
			$stmt->cache();
			$statements[] = $stmt;
		}
		return $statements;
	}

	public function getInsertId() {
		return $this->connection->lastInsertId();
	}

	public function escape($value) {
		return trim($this->connection->quote($value), "'"); // Remove the trim stuff
	}
	
}

class DB {
	
	private static $instance = null;

	public static function getInstance() {
		return self::$instance;
	}

	public static function setInstance(Database $instance) {
		self::$instance = $instance;
	}

	public static function __callStatic($name, $arguments) {
		return call_user_func_array(array(self::$instance, $name), $arguments);
	}
	
}