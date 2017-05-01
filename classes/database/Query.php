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

abstract class Query {
	
	const INSERT = 1;
	const UPDATE = 2;
	const SELECT = 3;
	const DELETE = 4;
	const ALTER = 5;
	const CREATE = 6;
	const DROP = 7;

	const LIST_TABLES = 10;
	const LIST_COLUMNS = 11;
	
	const SCHEMA_CREATE = 21;
	const SCHEMA_DROP_CREATE = 22;
	const SCHEMA_INSERT = 23;
	
	const INNER_JOIN = 101;
	const LEFT_JOIN = 102;
	const RIGHT_JOIN = 103;
	const FULL_JOIN = 104;

	const ASC = 201;
	const DESC = 202;
	
	const _OR = 205;
	const _AND = 206;
	
	const ALIAS = 209;
	
	const IS = 211;
	const IS_NOT = 212;

	const LIKE = 221;
	const UNLIKE = 222;
	
	const IN = 231;
	const NOT_IN = 232;
	
	const ALIAS_SEP = '__';
	
	protected $connection = null;
	protected $type;
	protected $table;
	protected $values = array();
	protected $orderBy = array();
	protected $groupBy = array();
	protected $limit = null;
	protected $offset = null;
	protected $joins = array();
	protected $distinct = false;
	protected $where = null;
	protected $having = null;
	protected $safeAction = true;
	
	public function __construct($connection = null) {
		$this->connection = $connection instanceof Database ? $connection : Database::getInstance();
	}
	
	public function getTable() {
		return $this->table;
	}
	
	protected function addPrefix($table) {
		return $this->connection->getPrefix() . $table;
	}
	
	public function insert($table, array $data) {
		$this->type = self::INSERT;
		$this->table = $table;
		$this->values = $data;
		return $this;
	}
	
	public function update($table, array $data) {
		$this->type = self::UPDATE;
		$this->table = $table;
		$this->values = $data;
		return $this;
	}
	
	public function select($table, array $columns = array()) {
		$this->type = self::SELECT;
		$this->table = $table;
		$this->values = $columns;
		return $this;
	}
	
	public function distinct() {
		$this->distinct = true;
	}
	
	public function col($column) {
		$this->values[] = $column;
	}
	
	public function colRaw($column) {
		$this->values[] = new QueryRaw($column);
	}
	
	public function delete($table) {
		$this->type = self::DELETE;
		$this->table = $table;
		return $this;
	}
	
	public function alter($table) {
		$this->type = self::ALTER;
		$this->table = $table;
		return $this;
	}
	
	public function create($table, $ifNotExists = true) {
		$this->type = self::CREATE;
		$this->table = $table;
		$this->safeAction = $ifNotExists;
		return $this;
	}
	
	public function drop($table, $ifExists = true) {
		$this->type = self::DROP;
		$this->table = $table;
		$this->safeAction = $ifExists;
		return $this;
	}
	
	public function schemaStructure($table, $drop = true) {
		$this->type = $drop ? self::SCHEMA_DROP_CREATE : self::SCHEMA_CREATE;
		$this->table = $table;
		return $this;
	}
	
	public function schemaData($table) {
		$this->type = self::SCHEMA_INSERT;
		$this->table = $table;
		return $this;
	}
	
	public function listTables() {
		$this->type = self::LIST_TABLES;
		return $this;
	}
	
	public function listColumns($table) {
		$this->type = self::LIST_COLUMNS;
		$this->table = $table;
		return $this;
	}
	
	protected function join($type, $table, $tableKey, $otherTableKey, $alias = null) {
		$join = new QueryJoin();
		$join->type = $type;
		
		$join->table = $table;
		$join->alias = $alias === null ? $table : $alias;
		$join->tableKey = $tableKey;

		$join->otherTableKey = $otherTableKey;

		$this->joins[] = $join;
	}
	
	public function innerJoin($table, $tableKey, $otherTableKey, $alias = null) {
		$this->join(self::INNER_JOIN, $table, $tableKey, $otherTableKey, $alias);
		return $this;
	}
	
	public function leftJoin($table, $tableKey, $otherTableKey, $alias = null) {
		$this->join(self::LEFT_JOIN, $table, $tableKey, $otherTableKey, $alias);
		return $this;
	}
	
	public function rightJoin($table, $tableKey, $otherTableKey, $alias = null) {
		$this->join(self::RIGHT_JOIN, $table, $tableKey, $otherTableKey, $alias);
		return $this;
	}
	
	public function fullJoin($table, $tableKey, $otherTableKey, $alias = null) {
		$this->join(self::FULL_JOIN, $table, $tableKey, $otherTableKey, $alias);
		return $this;
	}
	
	public function sortAsc($columns) {
		$this->sort($columns, self::ASC);
		return $this;
	}
	
	public function sortDesc($columns) {
		$this->sort($columns, self::DESC);
		return $this;
	}
	
	protected function sort($columns, $order) {
		if (is_string($columns)) {
			$columns = array($columns);
		}
		foreach($columns as $column) {
			$this->orderBy[$column] = $order;
		}
	}
	
	public function groupBy($columns) {
		if (is_string($columns)) {
			$columns = array($columns);
		}
		foreach($columns as $column) {
			$this->orderBy[] = $column;
		}
		return $this;
	}
	
	public function limit($limit) {
		$this->limit = $limit;
		return $this;
	}
	
	public function offset($offset) {
		$this->offset = $offset;
		return $this;
	}
	
	/**
	 * 
	 * @param $type
	 * @param $column
	 * @param $op If no $value is specified, $op can be the value and the $op is assumed to be '='.
	 * @param $value Optional
	 * @return $this
	 */
	protected function condition() {
		$op = '=';
		if (func_num_args() == 2) {
			list($type, $data) = func_get_args();
			foreach ($data as $column => $value) {
				$this->condition($type, $column, $value);
			}
			return $this;
		}
		else if (func_num_args() == 3) {
			list($type, $column, $value) = func_get_args();
		}
		else if (func_num_args() == 4) {
			list($type, $column, $op, $value) = func_get_args();
		}
		else {
			trigger_error('Invalid number of arguments specified.', E_USER_WARNING);
		}

		if ($this->{$type} === null) {
			$this->{$type} = $this->makeConditionGroupObject($type, self::_AND);
		}
		$this->{$type}->conditions[] = $this->makeConditionObject($type, $column, $op, $value);
		return $this;
	}
	
	protected function conditionIn($type, $column, $values) {
		return $this->condition($type, $column, self::IN, $values);
	}
	
	protected function conditionNotIn($type, $column, $values) {
		return $this->condition($type, $column, self::NOT_IN, $values);
	}
	
	protected function conditionUnlike($type, $column, $value) {
		return $this->condition($type, $column, self::UNLIKE, $value);
	}
	
	protected function conditionLike($type, $column, $value) {
		return $this->condition($type, $column, self::LIKE, $value);
	}
	
	protected function conditionNotNull($type, $column) {
		return $this->condition($type, $column, self::IS_NOT, null);
	}
	
	protected function conditionNull($type, $column) {
		return $this->condition($type, $column, self::IS, null);
	}
	
	protected function conditionAnd($type, $callback) {
		return $this->conditionGroup ($type, self::_AND, $callback);
	}

	protected function conditionOr($type, $callback) {
		return $this->conditionGroup ($type, self::_OR, $callback);
	}
	
	protected function conditionGroup($type, $op, $callback) {
		$subQuery = new static();
		$callback($subQuery);
		$conditions = call_user_func(array($subQuery, 'get' . ucfirst($type)));
		$this->{$type}->conditions[] = $this->makeConditionGroupObject($type, $op, $conditions);
		return $this;
	}
	
	protected function makeConditionGroupObject($type, $op, array $conditions = array()) {
		$conditionGroup = new QueryConditionGroup();
		$conditionGroup->op = $op;
		$conditionGroup->conditions = $conditions;
		return $conditionGroup;
	}
	
	protected function makeConditionObject($type, $column, $op, $value) {
		$condition = new QueryCondition();
		$condition->column = $column;
		$condition->op = $op;
		$condition->value = $value;
		return $condition;
	}
	
	protected function makeQueryObject($query, $values = array()) {
		$obj = new QueryPart();
		$obj->query = $query;
		$obj->values = $values;
		return $obj;
	}

	public function __call($name, $arguments) {
		$type = null;
		$method = '';
		if (strpos($name, 'where') === 0) {
			$type = 'where';
			if (strlen($name) > 5) {
				$method = lcfirst(substr($name, 5));
			}
		}
		else if (strpos($name, 'having') === 0) {
			$type = 'having';
			if (strlen($name) > 6) {
				$method = lcfirst(substr($name, 6));
			}
		}
		
		if ($type !== null) {
			$callable = array($this, 'condition' . $method);
			if (is_callable($callable)) {
				array_unshift($arguments, $type);
				return call_user_func_array($callable, $arguments);
			}
		}

		trigger_error("Inaccessible method '{$name}'", E_USER_NOTICE);
	}

	public static function __callStatic($name, $arguments) {
		$instance = new static();
		// Call the non-static method from the class instance
		return call_user_func_array(array($instance, $name), $arguments);
	}
	
	public function build() {
		switch($this->type) {
			case self::INSERT:
				return $this->buildInsert();
			case self::UPDATE:
				return $this->buildUpdate();
			case self::SELECT:
				return $this->buildSelect();
			case self::DELETE:
				return $this->buildDelete();
			case self::ALTER:
				return $this->buildAlter();
			case self::CREATE:
				return $this->buildCreate();
			case self::DROP:
				return $this->buildDrop();
			case self::LIST_TABLES:
				return $this->buildListTables();
			case self::LIST_COLUMNS:
				return $this->buildListColumns();
			case self::SCHEMA_CREATE:
			case self::SCHEMA_DROP_CREATE:
				return $this->buildSchemeStructure();
			case self::SCHEMA_INSERT:
				return $this->buildSchemeData();
			default:
				trigger_error('No query type specified', E_USER_WARNING);
				return null;
		}
	}
	
	public function fetch() {
		$stmt = $this->execute();
		if ($stmt) {
			return $stmt->fetch();
		}
		else {
			return false;
		}
	}
	
	public function fetchObject($class = null) {
		$stmt = $this->execute();
		if ($stmt) {
			return $stmt->fetchObject($class);
		}
		else {
			return false;
		}
	}
	
	public function fetchOne($colum = 1) {
		$stmt = $this->execute();
		if ($stmt) {
			return $stmt->fetchOne($column);
		}
		else {
			return false;
		}
	}
	
	public function fetchMatrix() {
		$stmt = $this->execute();
		if ($stmt) {
			return $stmt->fetchMatrix();
		}
		else {
			return false;
		}
	}
	
	function fetchObjectMatrix($class = null) {
		$stmt = $this->execute();
		if ($stmt) {
			return $stmt->fetchObjectMatrix($class);
		}
		else {
			return false;
		}
	}
	
	function fetchList($column = 1) {
		$stmt = $this->execute();
		if ($stmt) {
			return $stmt->fetchList($column);
		}
		else {
			return false;
		}
	}
	
	public function execute() {
		if ($this->connection === null) {
			return false;
		}
		$query = $this->build();
		if ($query === null) {
			return false;
		}
		
		return $this->connection->execute($query->query, $query->values);
	}

	protected function formatNames(array $names) {
		$alias = !empty($this->joins);
		$as = $this->translateKeyword(self::ALIAS);
		foreach($names as $key => $value) {
			if (is_string($value)) {
				$names[$key] = $this->formatColumn($value);
				if ($alias) {
					$names[$key] .= " {$as} " . $this->formatColumn($value, true);
				}
			}
		}
		return implode(', ', $names);
	}
	
	protected function formatTable($table) {
		return $this->escapeTable($this->addPrefix($table));
	}
	
	protected function formatColumn($column, $alias = false) {
		// In case we have joins the columns might not be unique, therefore we need to prepend the alias name
		if (!empty($this->joins) && strpos($column, '.') === false) {
			$column = "{$this->table}.{$column}";
		}
		if ($alias) {
			return str_replace('.', self::ALIAS_SEP, $column);
		}
		else {
			return $this->escapeColumn($column);
		}
	}

	protected abstract function escapeDatabase($database);
	protected abstract function escapeTable($table);
	protected abstract function escapeColumn($column);
	protected abstract function buildInsert();
	protected abstract function buildUpdate();
	protected abstract function buildSelect();
	protected abstract function buildDelete();
	protected abstract function buildAlter();
	protected abstract function buildCreate();
	protected abstract function buildDrop();
	protected abstract function buildConditions($group);
	protected abstract function buildLimitOffset();
	protected abstract function buildJoins();
	protected abstract function buildListTables();
	protected abstract function buildListColumns();
	protected abstract function buildSchemaStructure();
	protected abstract function buildSchemaData();
	protected abstract function buildComment($comment);
	protected abstract function translateKeyword($index);
	
	public function __toString() {
		return $this->build();
	}
	
}

class QueryRaw {
	
	private $raw;
	
	public function __construct($expression) {
		$this->raw = $expression;
	}
	
	public function __toString() {
		return $this->raw;
	}
	
}

class QueryJoin {
	
	public $type;
	public $table;
	public $alias;
	public $tableKey;
	public $otherTableKey;
	
}

class QueryConditionGroup {
	
	public $op;
	public $conditions = array();
	
}

class QueryCondition {

	public $column;
	public $op;
	public $value;
	
}

class QueryPart {
	
	public $query;
	public $values = array();
	
}