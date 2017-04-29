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

class MysqlQuery extends Query {

	public function __construct($connection = null) {
		parent::__construct($connection);
	}

	protected function buildAlter() {
		throw new NotImplementedException(); // ToDo: Implement
	}

	protected function buildComment($comment) {
		$code = '';
		$lines = preg_split('~\r\n|\r|\n~', ' ', $comment);
		foreach ($lines as $line) {
			$code .= '-- ' . $line . PHP_EOL;
		}
		return $code;
	}

	protected function buildConditions($group) {
		if (!($group instanceof QueryConditionGroup) || empty($group->conditions)) {
			return null;
		}
		$queries = array();
		$values = array();
		foreach ($group->conditions as $condition) {
			$part = null;
			if ($condition instanceof QueryConditionGroup) {
				$part = $this->buildConditions($condition);
				$queries[] = $part->query;
				$values[] = array_merge($values, $part->values);
			} else if ($condition instanceof QueryCondition) {
				if ($condition->op == self::IN || $condition->op == self::NOT_IN) {
					$placeholders = implode(',', array_fill(0, count($condition->value), '?'));
					$queries[] = $this->formatColumn($condition->column) . ' ' . $this->translateKeyword($condition->op) . "({$placeholders})";
					$values[] = array_merge($values, array_values($condition->value));
				}
				else {
					if ($condition->op == '!=') {
						$condition->op = '<>';
					}

					$queries[] = $this->formatColumn($condition->column) . ' ' . $condition->op . ' ?';
					$values[] = $condition->value;
				}
			}
		}
		$query = '(' . implode(' ' . $this->translateKeyword($group->op) . ' ', $queries) . ')';
		return $this->makeQueryObject($query, $values);
	}

	protected function buildDrop() {
		return $this->makeQueryObject('DROP TABLE '($this->safeAction ? 'IF EXISTS ' : '') . $this->formatTable($this->getTable()) . ';');
	}

	protected function buildCreate() {
		$sql = 'CREATE TABLE '($this->safeAction ? 'IF NOT EXISTS ' : '') . $this->formatTable($this->getTable()) . ' (...);';
		throw new NotImplementedException(); // ToDo: Implement
	}

	protected function buildDelete() {
		$values = array();
		$table = $this->formatTable($this->getTable());
		$query = "DELETE FROM {$table}";

		$conditions = $this->buildConditions($this->where);
		if ($conditions !== null) {
			$query .= ' ' . $conditions->query;
			$values = $conditions->values;
		}

		$limit = $this->buildLimitOffset(false);
		if (!empty($limit)) {
			$query .= ' ' . $limit;
		}

		$query .= ';';

		return $this->makeQueryObject($query, $values);
	}

	protected function buildInsert() {
		$table = $this->formatTable($this->getTable());
		$keys = $this->formatNames(array_keys($this->values));
		$placeholder = implode(',', array_fill(0, count($this->values), '?'));
		$query = "INSERT INTO {$table} ({$keys}) VALUES ({$placeholder});";
		return $this->makeQueryObject($query, array_values($this->values));
	}

	protected function buildJoins() {
		$joins = array();
		foreach ($this->joins as $join) {
			$table = $this->formatTable($join->table);
			$tableKey = $this->formatColumn($join->table . '.' . $join->tableKey);
			$otherTableKey = $this->formatColumn($join->otherTableKey);
			$joins[] = $this->translateKeyword($join->type) . " {$table} AS {$join->table} ON {$tableKey} = {$otherTableKey}";
		}
		return implode(' ', $joins);
	}

	protected function buildLimitOffset($offset = true) {
		$parts = array();
		if ($this->limit !== null) {
			$parts[] = "LIMIT {$this->limit}";
		}
		if ($offset && $this->offset !== null) {
			$parts[] = "OFFSET {$this->offset}";
		}
		return implode(' ', $parts);
	}
	

	protected function buildListTables() {
		return $this->makeQueryObject('SHOW TABLES FROM ' . $this->escapeDatabase($this->connection->getDatabase()) . ';'));
	}

	protected function buildListColumns() {
		return $this->makeQueryObject('SHOW COLUMNS FROM ' . $this->formatTable($this->table) . ';'));
	}

	public function buildSchemaStructure() {
		$drop = ($this->type == SCHEMA_DROP_CREATE);
		$table = $this->formatTable($this->table);

		$sql = '';
		if ($drop) {
			$sql .= PHP_EOL . static::comment('Delete: ' . $table)->build() . PHP_EOL;
			$sql .= static::drop($this->table)->build() . PHP_EOL;
		}
		$sql .= PHP_EOL . static::comment('Create: ' . $table)->build() . PHP_EOL;

		// Activate Quotes in sql names
		$this->connection->query('SET SQL_QUOTE_SHOW_CREATE = 1');
		$create = $this->connection->fetch_one($this->query('SHOW CREATE TABLE ' . $table), 2);
		if (empty($create)) {
			return null;
		}

		$sql .= $create . ';';
		return $this->makeQueryObject(trim($sql));
	}

	public function buildSchemaData() {
		$table = $this->formatTable($this->table);
		$table_data = PHP_EOL . static::comment('Data: ' . $table . ($this->offset !== null && $this->limit !== null ? ' {' . $this->offset . ', ' . ($this->offset + $this->limit) . '}' : '')) . PHP_EOL;
		$query = static::select($this->table)->limit($this->limit)->offset($this->offset)->build();
		$result = $this->connection->query($query);
		while ($row = $this->connection->fetch_assoc($result)) {
			$table_data .= static::insert($table, $row)->build() . PHP_EOL;
		}
		return $this->makeQueryObject(trim($table_data));
	}

	protected function buildSelect() {
		$values = array();

		// SELECT
		$parts = array('SELECT');
		if ($this->distinct) {
			$parts[] = 'DISTINCT';
		}
		$names = $this->formatNames($this->values);
		if (empty($names)) {
			$names = '*';
		}
		$parts[] = $names;

		// FROM
		$table = $this->formatTable($this->getTable());
		$alias = $this->getTable();
		$parts[] = "FROM {$table} AS {$alias}";

		// JOINS
		$joins = $this->buildJoins();
		if (!empty($joins)) {
			$parts[] = $joins;
		}

		// WHERE
		$conditionsWhere = $this->buildConditions($this->where);
		if ($conditionsWhere !== null) {
			$parts[] = 'WHERE ' . $conditionsWhere->query;
			$values = array_merge($values, $conditionsWhere->values);
		}

		// GROUP BY
		if (count($this->groupBy) > 0) {
			$groupBy = implode(', ', array_map(array($this, 'formatColumn'), $this->groupBy));
			$parts[] = 'GROUP BY ' . $groupBy;
		}

		// HAVING
		$conditionsHaving = $this->buildConditions($this->having);
		if ($conditionsHaving !== null) {
			$parts[] = 'HAVING ' . $conditionsHaving->query;
			$values = array_merge($values, $conditionsWhere->values);
		}

		// ORDER BY
		if (count($this->orderBy) > 0) {
			$orderBy = array();
			foreach ($this->orderBy as $column => $sortOrder) {
				$orderBy[] = $this->formatColumn($column) . ' ' . $this->translateKeyword($sortOrder);
			}
			$parts[] = 'ORDER BY ' . implode(' ', $orderBy);
		}

		// LIMIT OFFSET
		$limitOffset = $this->buildLimitOffset();
		if (!empty($limitOffset)) {
			$parts[] = $limitOffset;
		}

		return $this->makeQueryObject(implode(' ', $parts) . ';', $values);
	}

	protected function buildUpdate() {
		$kvp = array();
		foreach ($this->values as $key => $_) {
			$kvp[] = $this->formatColumn($key) . ' = ?';
		}
		$setter = implode(', ', $kvp);

		$table = $this->formatTable($this->getTable());
		$query = "UPDATE {$table} SET {$setter}";
		$values = $this->values;

		$conditions = $this->buildConditions($this->where);
		if ($conditions !== null) {
			$query .= ' ' . $conditions->query;
			$values = array_merge($values, $conditions->values);
		}

		$limit = $this->buildLimitOffset(false);
		if (!empty($limit)) {
			$query .= ' ' . $limit;
		}

		$query .= ';';

		return $this->makeQueryObject($query, array_values($this->values));
	}

	protected function translateKeyword($index) {
		if (is_array($index)) {
			return array_map(array($this, 'translateKeyword'), $index);
		}

		switch ($index) {
			case self::INNER_JOIN:
				return 'INNER JOIN';
			case self::LEFT_JOIN:
				return 'LEFT JOIN';
			case self::RIGHT_JOIN:
				return 'RIGHT JOIN';
			case self::FULL_JOIN:
				throw new NotImplementedException();
			case self::ASC:
				return 'ASC';
			case self::DESC:
				return 'DESC';
			case self::IS:
				return 'IS';
			case self::IS_NOT:
				return 'IS NOT';
			case self::LIKE:
				return 'LIKE';
			case self::UNLIKE:
				return 'NOT LIKE';
			case self::IN:
				return 'IN';
			case self::NOT_IN:
				return 'NOT IN';
			case self::_OR:
				return 'OR';
			case self::_AND:
				return 'AND';
			default:
				return '';
		}
	}

	protected function escapeColumn($column) {
		if (strpos($column, '.') !== false) {
			list($table, $column) = explode('.', $column, 2);
			return $this->escapeTable($table) . ".`{$column}`";
		} else {
			return "`{$column}`";
		}
	}

	protected function escapeTable($table) {
		return "`{$table}`";
	}

	protected function escapeDatabase($database) {
		return "`{$database}`";
	}

}
