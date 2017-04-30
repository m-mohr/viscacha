<?php

namespace Viscacha\Model;

class BaseModel {

	/**
	 * Name of the table without prefix.
	 * @var string 
	 */
	protected $table = null;

	/**
	 * Name of the column which holds the primary key / unique identifier.
	 * 
	 * Can be also a combination of multiple primary keys in an array.
	 * 
	 * Default: id
	 * 
	 * @var string|array
	 */
	protected $primaryKey = 'id';

	/**
	 * Array containing all foreign keys / relations in this entity.
	 * 
	 * The key of the array is the column name and the value is the class name, e.g.
	 * 'user_id' => '\Viscacha\Model\User'
	 * 
	 * @var array 
	 */
	protected $foreignKeys = array();

	/**
	 * Array containing all entities that have foreign keys to this entity.
	 * 
	 * The value of the array is the column name and the key is the class name, e.g.
	 * '\Viscacha\Model\User' => 'user_id'
	 * 
	 * @var array 
	 */
	protected $belongsTo = array();

	/**
	 * An array containing all column names.
	 * 
	 * @var array 
	 */
	protected $columns = array();

	/**
	 * The column that holds the information about the soft delete state.
	 *
	 * Data type of this column must be: INTEGER(10) NULL (holds a unix timestamp).
	 * Set to null to disable this feature.
	 * 
	 * @var string 
	 */
	protected $softDelete = null;

	/**
	 * The column that holds the information about the last modification.
	 *
	 * Data type of this column must be: INTEGER(10) NULL (holds a unix timestamp).
	 * Set to null to disable this feature.
	 * 
	 * @var string 
	 */
	protected $updateTimestamp = null;

	/**
	 * The column that holds the information about the creation.
	 *
	 * Data type of this column must be: INTEGER(10) NULL (holds a unix timestamp).
	 * Set to null to disable this feature.
	 * 
	 * @var string 
	 */
	protected $createTimestamp = null;

	/**
	 * Array containing the initial data from the database.
	 * 
	 * @var array 
	 */
	protected $oldData = array();

	/**
	 * Array containing the current data from the database.
	 * 
	 * @var array 
	 */
	protected $data = array();
	
	protected $relationData = array();
	protected $query = null;
	protected $mapping = array();

	public function __construct() {
		$this->data = array_fill_keys($this->columns, null);
		$this->oldData = $this->data;
	}

	public function getPrimaryKey() {
		return $this->primaryKey;
	}
	
	public function getTableName() {
		return $this->table;
	}
	
	public function getForeignKeyClass($key) {
		if (isset($this->foreignKeys[$key])) {
			return $this->foreignKeys[$key];
		}
		return null;
	}
	
	public function getForeignKeys($key) {
		return $this->foreignKeys;
	}
	
	public function getColumns() {
		return $this->columns;
	}
	
	public function hasColumn($column) {
		return isset($this->columns[$column]);
	}

	public function hasData($column) {
		return isset($this->data[$column]) && $this->data[$column] !== null;
	}

	public function changedColumns() {
		return array_diff_assoc($array1, $array2); // TODO
	}

	public function isNew() {
		if (is_array($this->primaryKey) && count($this->primaryKey) > 0) {
			foreach ($this->primaryKey as $pk) {
				if (!$this->hasData($pk)) {
					return false;
				}
			}
			return true;
		} else if (is_string($this->primaryKey)) {
			return $this->hasData($this->primaryKey);
		}
		if ($this->primaryKey !== null) {
			trigger_error("Invalid primary key specified.", E_USER_NOTICE);
		}
		return true;
	}

	public function save() {
		if ($this->isNew()) {
			$this->add();
		} else {
			$this->update();
		}
	}

	protected function update() {
		
	}

	protected function add() {
		
	}

	public function delete($force = false) {
		
	}

	public function retrieve($relations = false) {
		
	}

	protected function filterPrimaryKey() {
		if ($this->isNew()) {
			trigger_error("No data for primary key set", E_USER_ERROR);
		}
		if (is_string($this->primaryKey)) {
			$this->query->where($this->primaryKey, '=', $this->data[$this->primaryKey]);
		} else if (is_array($this->primaryKey) && count($this->primaryKey) > 0) {
			foreach ($this->primaryKey as $pk) {
				$this->query->where($pk, '=', $this->data[$pk]);
			}
		}
		return $this;
	}
	
	private function addJoin(BaseModel $model, $column) {
		$foreignTable = $model->getTableName();
		$alias = $foreignTable;
		$foreignPk = $model->getPrimaryKey();
		while(isset($this->mapping[$alias])) {
			$alias .= "x";
		}
		$this->mapping[$alias] = get_class($model);
		$this->discreteSelect($model, $alias);
		$this->query->leftJoin($foreignTable, $foreignPk, $column, $alias);
	}
	
	public function injectData($data) {
		// ToDo: ...
	}

	public function expand($columns) {
		$addedColumns = array();
		foreach ($columns as $column) {
			$path = explode('.', $column);
			$model = $this;
			foreach ($path as $k) {
				$table = $model->getTableName();
				$class = $model->getForeignKeyClass($k);
				if ($class) {
					$model = new $class();
					if (!in_array($k, $addedColumns)) {
						$this->addJoin($model, $table . '.' . $k);
						$addedColumns[] = $k;
					}
				}
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

	protected function query() {
		$this->query = \DB::builder();
		return $this;
	}

	public static function __callStatic($name, $arguments) {
		$instance = new static();
		// Call the non-static method from the class instance
		return call_user_func_array(array($instance, $name), $arguments);
	}

	public function __call($name, $arguments) {
		// Getter and Setter for columns
		if (strlen($name) > 3) {
			$prefix = substr($name, 0, 3);
			$column = lcfirst(substr($name, 3));
			if ($this->hasColumn($column)) {
				if ($prefix === 'set') {
					if (!isset($arguments[0])) {
						trigger_error("Parameter not specified for method '{$name}'", E_USER_NOTICE);
					}
					$this->data[$column] = $arguments[0];
				} else if ($prefix === 'get') {
					return $this->data[$column];
				}
			}
		}

		// Redirect to Query Builder
		if ($this->query !== null) {
			$callable = array($this->query, $name);
			if (is_callable($callable)) {
				$result = call_user_func_array($callable, $arguments);
				if ($result === $this->query) {
					return $this;
				} else {
					return $result;
				}
			}
		}

		trigger_error("Inaccessible method '{$name}'", E_USER_NOTICE);
	}

	public function __set($name, $value) {
		if (array_key_exists($name, $this->data)) {
			$setter = 'set' . ucfirst($name);
			$this->{$setter}($value);
		}

		trigger_error("Inaccessible property '{$name}'", E_USER_NOTICE);
	}

	public function __get($name) {
		if (array_key_exists($name, $this->data)) {
			$getter = 'get' . ucfirst($name);
			$this->{$getter}($value);
		}

		trigger_error("Inaccessible property '{$name}'", E_USER_NOTICE);
	}

	public function __isset($name) {
		return $this->hasColumn($name);
	}

	public function __unset($name) {
		return $this->{$name} = null;
	}

}