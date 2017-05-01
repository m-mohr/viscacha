<?php

namespace Viscacha\Model;

class Model implements \ArrayAccess {

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
	 * Array containing the initial data from the database.
	 * 
	 * @var array 
	 */
	protected $originalData = array();

	/**
	 * Array containing the current data from the database.
	 * 
	 * @var array 
	 */
	protected $data = array();
	
	/**
	 * Cached data from relations (belongsTo, ...)
	 * 
	 * @var array
	 */
	protected $relationData = array();

	public function __construct($primaryKey = null) {
		$this->data = array_fill_keys($this->columns, null);
		$this->syncOriginal();
		if ($primaryKey !== null) {
			$this->setPrimaryKeyData($primaryKey);
		}
	}

	public static function create() {
		return new static();
	}

	public static function find($primaryKey) {
		$model = new static($primaryKey);
		$model->load();
		return $model;
	}

	public function getPrimaryKey() {
		return $this->primaryKey;
	}

	public function isPrimaryKey($column) {
		return (is_string($this->primaryKey) && $this->primaryKey == $column) || (is_array($this->primaryKey) && in_array($column, $this->primaryKey));
	}

	private function setPrimaryKeyData($primaryKey) {
		if (is_string($this->primaryKey)) {
			$this->data[$this->primaryKey] = $primaryKey;
		} else if (is_array($this->primaryKey) && count($this->primaryKey) > 0) {
			foreach ($this->primaryKey as $pk) {
				$this->data[$pk] = $primaryKey[$pk];
			}
		}

		throw new NoPrimaryKeyException();
	}

	public function getPrimaryKeyData() {
		if ($this->isNew()) {
			throw new NoPrimaryKeyValueException();
		}
		if (is_string($this->primaryKey)) {
			return [$this->primaryKey, $this->data[$this->primaryKey]];
		} else if (is_array($this->primaryKey) && count($this->primaryKey) > 0) {
			$pks = array();
			foreach ($this->primaryKey as $pk) {
				$pks[$pk] = $this->data[$pk];
			}
			return $pks;
		}

		throw new NoPrimaryKeyException();
	}

	public function getTableName() {
		return $this->table;
	}

	public function isForeignKey($column) {
		return isset($this->foreignKeys[$column]);
	}

	public function getForeignKeyClass($column) {
		if (isset($this->foreignKeys[$column])) {
			return $this->foreignKeys[$column];
		}
		return null;
	}

	public function getForeignKeys() {
		return $this->foreignKeys;
	}

	public function getColumns() {
		return $this->columns;
	}

	public function hasColumn($column) {
		return in_array($column, $this->columns);
	}

	public function hasData($column) {
		return isset($this->data[$column]) && $this->data[$column] !== null;
	}

	public function changedData() {
		$changes = array();
		foreach ($this->data as $key => $value) {
			if (!isset($this->originalData[$key])) {
				continue;
			}

			if ($this->originalData[$key] !== $value) {
				$changes[$key] = $value;
			}
		}
		return $changes;
	}

	protected function syncOriginal() {
		$this->originalData = $this->data;
	}

	public function query() {
		return \DB::builder();
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
		throw new NoPrimaryKeyException();
	}

	public function save() {
		$changed = $this->changedData();
		if (empty($changed)) {
			return true;
		}

		if ($this->isNew()) {
			$this->query()->insert($this->table, $changed)->where($this->getPrimaryKeyData())->execute();
		} else {
			$this->query()->update($this->table, $changed)->where($this->getPrimaryKeyData())->execute();
		}

		$this->syncOriginal();
	}

	public function recover() {
		if ($this->softDelete) {
			$this->query()->update($this->table, [$this->softDelete => null])->where($this->getPrimaryKeyData())->execute();
			$this->originalData[$this->softDelete] = null;
		}
	}

	public function remove($force = false) {
		if ($this->softDelete && !$force) {
			$time = times();
			$this->query()->update($this->table, [$this->softDelete => $time])->where($this->getPrimaryKeyData())->execute();
			$this->originalData[$this->softDelete] = $time;
		} else {
			$this->query()->delete($this->table)->where($this->getPrimaryKeyData())->execute();
			$this->originalData = array();
		}
	}

	public function load() {
		$this->query()->select($this->table)->where($this->getPrimaryKeyData())->fetchObject($this);
	}
	
	public function injectRelationData($column, Model $model) {
		$this->relationData[$column] = $model;
	}

	public function injectData($data, $syncOriginal = false) {
		foreach ($data as $column => $value) {
			if ($this->hasColumn($column)) {
				$this->data[$column] = $value;
			}
		}

		if ($syncOriginal) {
			$this->syncOriginal();
		}
	}

	protected function hasMany($class, $column) {
		$pkData = $this->getPrimaryKeyData();
		if (count($pkData) > 1) {
			trigger_error('Relations with multiple primary keys are not implemented.', E_USER_ERROR);
		}
		$model = new $class();
		return $model->query()->select($model->getTableName())->where($column, reset($pkData))->fetchObjectMatrix($class);
	}

	protected function hasOne($class, $column) {
		$models = $this->hasMany($class, $column);
		if (!empty($models)) {
			return $models[0];
		}
		return false;
	}

	protected function belongsTo($class, $column) {
		if (isset($this->relationData[$column])) {
			return $this->relationData[$column];
		}

		$model = new $class();
		$pk = $model->getPrimaryKey();
		if (count($pk) > 1) {
			trigger_error('Relations with multiple primary keys are not implemented.', E_USER_ERROR);
		}
		$object = $model->query()->select($model->getTableName())->where($pk, $this->data[$column])->fetchObject($class);
		$this->relationData[$column] = $object;
		return $object;
	}

	public function __call($name, $arguments) {
		// Getter and Setter for columns
		// See also: BaseModel::__call()
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

		trigger_error("Inaccessible method '{$name}'", E_USER_NOTICE);
	}

	public function __set($name, $value) {
		if (in_array($name, $this->columns) && !$this->isPrimaryKey($name)) {
			$setter = 'set' . ucfirst($name);
			$this->{$setter}($value);
		}
	}

	public function __get($name) {
		if (in_array($name, $this->columns)) {
			$getter = 'get' . ucfirst($name);
			return $this->{$getter}();
		}
		return null;
	}

	public function __isset($name) {
		return $this->hasColumn($name);
	}

	public function __unset($name) {
		return $this->{$name} = null;
	}

	public function offsetSet($offset, $value) {
		return $this->__set($offset, $value);
	}

	public function offsetExists($offset) {
		return $this->__isset[$offset];
	}

	public function offsetUnset($offset) {
		$this->__unset($offset);
	}

	public function offsetGet($offset) {
		return $this->__get($offset);
	}

}

class NoPrimaryKeyValueException extends \Exception {
	
}

class NoPrimaryKeyException extends NoPrimaryKeyValueException {
	
}
