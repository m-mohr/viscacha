<?php

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

	public function expand($columns) {
		$this->discreteSelect($this, $this->getTableName());
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
				$this->discreteSelect($model, $alias);
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
				if ($prefix === 'set') {
					if (!isset($arguments[0])) {
						trigger_error("Parameter not specified for method '{$name}'", E_USER_NOTICE);
					}
					$this->data[$column] = $arguments[0];
					return;
				} else if ($prefix === 'get') {
					return $this->data[$column];
				}
			}
		}
		
		// Getter for alias based data retrieved from the relations / joins
		if (isset($this->relationData[$name])) {
			return $this->relationData[$name];
		}

		// Redirect to Query Builder
		$callable = array($this->query, $name);
		if (is_callable($callable)) {
			$result = call_user_func_array($callable, $arguments);
			if ($result === $this->query) {
				return $this;
			} else {
				return $result;
			}
		}

		trigger_error("Inaccessible method '{$name}'", E_USER_NOTICE);
	}

}

/**
 * Decorator for Result objects to pass Model mapping information.
 */
class ModelResult {
	
	private $result = null;
	private $baseModel;
	private $columnMap;
	
	public function __construct(BaseModel $baseModel) {
		$this->baseModel = $baseModel;
		$table = $this->baseModel->getTableName();
		$this->addModelMapping($table, '', get_class($this->baseModel));
	}
	
	public function setResult(Result $result) {
		$this->result = $result;
	}
	
	public function addModelMapping($alias, $column, $class) {
		$table = $this->baseModel->getTableName();
		$column = empty($column) ? $table : $table . '.' . $column;
		$this->columnMap[$column] = array('alias' => $alias, 'class' => $class, 'column' => $column);
	}
	
	public function fetchObjectMatrix() {
		$models = array();
		while ($model = $this->fetchObject()) {
			$models[] = $model;
		}
		return $models;
	}
	
	public function fetchObject() {
		return $this->createModels($this->fetch());
	}
	
	private function createModels($data) {
		if (empty($data)) {
			return false;
		}
		
		// Group data by alias
		foreach ($data as $key => $value) {
			$parts = explode(Query::ALIAS_SEP, $key, 2);
			if (count($parts) == 2) {
				$groups[$parts[0]][$parts[1]] = $value;
			}
			else {
				$groups[$this->baseModel->getTableName()][$parts[0]] = $value;
			}
		}

		// Inject data into newly created models
		foreach ($this->columnMap as $column => $data) {
			$model = new $data['class']();
			$model->injectData($groups[$data['alias']], true);
			$this->columnMap[$column]['model'] = $model;
		}
		
		// Cascade models
		ksort($this->columnMap);
		$root = array_shift($this->columnMap);
		$this->cascadeModels($root['model'], $root['column']);
		return $root['model'];
	}
	
	private function cascadeModels($parentModel, $parentPath, $level = 1) {
        foreach($this->columnMap as $column => $data) {
            $tree = explode('.', $column);
            if(!isset($tree[$level]) || strpos($column, $parentPath) !== 0) {
				continue;
			}

			$nextLevel = $level + 1;
			if (!isset($tree[$nextLevel])) {
				$parentModel->injectRelationData($tree[$level], $data['model']);
				$this->cascadeModels($data['model'], $data['column'], $nextLevel);
			}
        }
	}
	
    public function __call($method, $args) {
        return call_user_func_array(array($this->result, $method), $args);
    }
	
}