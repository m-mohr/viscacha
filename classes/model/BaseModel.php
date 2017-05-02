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

namespace Viscacha\Model;

use Viscacha\Database\Query;
use Viscacha\Database\Result;

class BaseModel extends Model {
	
	protected $query = null;
	protected $result = null;
	
	public function __construct($primaryKey = null) {
		parent::__construct($primaryKey);
		$this->query();
	}
	
	public static function all(array $columns = array()) {
		$model = new static();
		return $model->select($model->getTableName(), $columns);
	}
	
	public function query() {
		$this->result = new ModelResult($this);
		$this->query = parent::query();
		return $this->query;
	}

	public function expand($columns, $selectColumns = true) {
		if ($selectColumns) {
			$this->discreteSelect($this, $this->getTableName());
		}
		$addedColumns = array();
		foreach ($columns as $column => $alias) {
			$path = explode('.', $column);
			$model = $this;
			foreach ($path as $i => $k) {
				$thisPath = implode('.', array_slice($path, 0, $i+1));
				$parentPath = implode('.', array_slice($path, 0, $i));
				$class = $model->getForeignKeyClass($k);
				if (!$class) {
					continue;
				}
				$model = new $class();
				if (in_array($thisPath, $addedColumns)) {
					continue;
				}
				$colName = empty($parentPath) ? $k : $columns[$parentPath] . '.' . $k;
				if ($selectColumns) {
					$this->discreteSelect($model, $alias);
				}
				$this->query->leftJoin($model->getTableName(), $model->getPrimaryKey(), $colName, $alias);
				$this->result->addModelMapping($alias, $column, get_class($model));
				$addedColumns[] = $thisPath;
			}
		}
		return $this;
	}
	
	protected function discreteSelect(BaseModel $model = null, $alias = null) {
		if ($model === null) {
			$model = $this;
		}
		$alias = $alias === null ? $model->getTableName() : $alias;
		foreach ($model->getColumns() as $column) {
			$this->query->col("{$alias}.{$column}");
		}
		return $this;
	}

	public function filterPrimaryKey() {
		return $this->filterByPrimaryKey($this->query);
	}
	
	public function execute() {
		$stmt = $this->query->execute();
		if ($stmt instanceof Result) {
			$this->result->setResult($stmt);
			return $this->result;
		}
		return $stmt;
	}

	public function __call($name, $arguments) {
		// Getter and Setter for columns
		// See also: Model::__call()
		if (strlen($name) > 3) {
			$prefix = substr($name, 0, 3);
			$column = lcfirst(substr($name, 3));
			if ($this->hasColumn($column)) {
				if ($prefix == 'set') {
					if (!isset($arguments[0])) {
						throw new \InvalidArgumentException("Parameter not specified for method '{$name}'");
					}
					$this->data[$column] = $arguments[0];
					return;
				} else if ($prefix == 'get') {
					return $this->data[$column];
				}
			}
		}

		// Allow short syntax for scopes
		$method = 'scope' . ucfirst($name);
		if (method_exists($this, $method)) {
			array_unshift($arguments, $this->query);
			return call_user_func_array(array($this, $method), $arguments);
		}

		// Redirect to Query Builder
		$callable = array($this->query, $name);
		if (is_callable($callable)) {
			$result = call_user_func_array($callable, $arguments);
			if ($result instanceof Query) {
				return $this;
			} else {
				return $result;
			}
		}

		throw new \BadMethodCallException("Inaccessible method '{$name}'");
	}

}
