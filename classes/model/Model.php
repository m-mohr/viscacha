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

abstract class Model implements \ArrayAccess {

	/**
	 * Name of the table without prefix.
	 * @var string 
	 */
	protected $table = null;

	/**
	 * Name of the column which holds the primary key / unique identifier.
	 * 
	 * Default: id
	 * 
	 * @var string
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
	
	/**
	 * Rules used for validation.
	 * 
	 * @see \Viscacha\IO\Validate
	 * @var array
	 */
	protected $validationRules = array();
	
	/**
	 * Validator based on the $validationRules
	 * 
	 * @var \Viscacha\IO\Validate\Validator
	 */
	private $validationProcessor = null;
	
	/**
	 * Rules used to pre-process / filter the data.
	 * 
	 * @var array
	 */
	protected $filterRules = array();
	
	/**
	 *
	 * @var type 
	 */
	private $filterProcessor = null;

	public function __construct($primaryKey = null) {
		$this->define();
		$this->data = array_fill_keys($this->columns, null);
		if ($primaryKey !== null) {
			$this->setPrimaryKeyValue($primaryKey);
		}
		$this->syncOriginal();
	}

	protected abstract function define();

	public static function instance() {
		return new static();
	}

	public static function find($primaryKey) {
		$model = new static($primaryKey);
		$model->load();
		return $model;
	}
	
	public static function fromRequest(array $ids) {
		$collection = new ModelCollection();
		$errorCollection = new InvalidMassDataExceptionCollection();
		foreach ($ids as $i) {
			$collection[$i] = new static($i);
			try {
				$collection[$i]->fillFromRequest($i);
			} catch(\Viscacha\Model\InvalidMassDataException $e) {
				$errorCollection->add($i, $e);
			}
		}
		if ($errorCollection->count() > 0) {
			throw $errorCollection;
		}
		return $collection;
	}

	public function getPrimaryKey() {
		return $this->primaryKey;
	}

	public function isPrimaryKey($column) {
		return is_string($this->primaryKey) && $this->primaryKey == $column;
	}

	private function setPrimaryKeyValue($primaryKey) {
		if (is_string($this->primaryKey)) {
			$this->data[$this->primaryKey] = $primaryKey;
		}
	}

	public function getPrimaryKeyValue() {
		if ($this->isNew()) {
			return null;
		}
		if (is_string($this->primaryKey)) {
			return $this->data[$this->primaryKey];
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
		foreach ($this->columns as $column) {
			if (!array_key_exists($column, $this->originalData)) {
				continue;
			}

			if ($this->originalData[$column] !== $this->data[$column]) {
				$changes[$column] = $this->data[$column];
			}
		}
		return $changes;
	}
	
	protected function removeUnknownColumns($data) {
		return array_intersect_key($data, array_flip($this->columns));
	}

	protected function syncOriginal() {
		$this->originalData = $this->data;
	}

	public function query() {
		return \DB::builder();
	}

	public function isNew() {
		if (is_string($this->primaryKey)) {
			return !$this->hasData($this->primaryKey);
		}
		return true;
	}

	public function save() {
		$changed = $this->changedData();
		if (empty($changed)) {
			return true;
		}

		if ($this->isNew()) {
			$stmt = $this->query()->insert($this->table, $changed)->execute();
			if ($stmt) {
				$this->setPrimaryKeyValue(\DB::getInsertId());
			}
		} else {
			$stmt = $this->query()->update($this->table, $changed)->where($this->getPrimaryKey(), $this->getPrimaryKeyValue())->execute();
		}
		if ($stmt) {
			$this->syncOriginal();
			return true;
		}
		return false;
	}

	public function recover() {
		if ($this->softDelete) {
			$this->query()->update($this->table, [$this->softDelete => null])->where($this->getPrimaryKey(), $this->getPrimaryKeyValue())->execute();
			$this->originalData[$this->softDelete] = null;
		}
	}

	public function remove($force = false) {
		if ($this->softDelete && !$force) {
			$time = times();
			$this->query()->update($this->table, [$this->softDelete => $time])->where($this->getPrimaryKey(), $this->getPrimaryKeyValue())->execute();
			$this->originalData[$this->softDelete] = $time;
		} else {
			$this->query()->delete($this->table)->where($this->getPrimaryKey(), $this->getPrimaryKeyValue())->execute();
			$this->originalData = array();
		}
	}

	public function load() {
		$result = $this->query()->select($this->table)->where($this->getPrimaryKey(), $this->getPrimaryKeyValue())->fetchObject($this);
		return ($result !== false);
	}
	
	public function getValidator() {
		// Lazy loading to avoid unneccessary parsing of validation rules
		if ($this->validationProcessor === null) {
			$this->validationProcessor = new \Viscacha\IO\Validate\Validator($this->validationRules);
			$this->addModelValidators();
			$this->defineCustomValidators();
		}
		return $this->validationProcessor;
	}

	protected function addModelValidators() {
		$methods = get_class_methods($this);
		$prefix = 'validate';
		foreach ($methods as $method) {
			if (\Str::startsWith($method, $prefix)) {
				$name = \Str::lcfirst(\Str::substr($method, \Str::length($prefix)));
				$this->validationProcessor->addProcessor($name, array($this, $method));
			}
		}
	}
	
	public function defineCustomValidators() {}
	
	public function getFilter() {
		// Lazy loading to avoid unneccessary parsing of filter rules
		if ($this->filterProcessor === null) {
			$this->filterProcessor = new \Viscacha\IO\Validate\FilterProcessor($this->filterRules);
			$this->defineCustomFilters();
		}
		return $this->filterProcessor;
	}
	
	public function defineCustomFilters() {}
	
	public function fillFromRequest($index = null) {
		if ($index !== null) {
			return $this->fill(array_column_assoc($_REQUEST, $index));
		}
		else {
			return $this->fill($_REQUEST);
		}
	}
	
	/**
	 * 
	 * @param array $data
	 * @return $this
	 * @throws InvalidMassDataException
	 */
	public function fill(array $data) {
		$data = $this->getFilter()->process($data);
		$validator = $this->getValidator();
		if ($validator->validate($data)) {
			$this->data = array_merge($this->data, $data);
		}
		else {
			throw new InvalidMassDataException($validator->getGroupedErrors());
		}
		return $this;
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
		$model = new $class();
		$list = $model->query()->select($model->getTableName())->where($column, $this->getPrimaryKeyValue())->fetchObjectMatrix($class);
		return new ModelCollection($list);
	}

	protected function hasOne($class, $column) {
		$models = $this->hasMany($class, $column);
		if (!empty($models)) {
			return $models[0];
		}
		return false;
	}

	protected function belongsTo($column) {
		if (isset($this->relationData[$column])) {
			return $this->relationData[$column];
		}

		$class = $this->foreignKeys[$column];
		$model = new $class();
		$object = $model->query()->select($model->getTableName())->where($model->getPrimaryKey(), $this->data[$column])->fetchObject($class);
		$this->injectRelationData($column, $object);
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
						throw new \BadMethodCallException("Parameter not specified for method '{$name}'");
					}
					$this->data[$column] = $arguments[0];
					return;
				} else if ($prefix === 'get') {
					return $this->data[$column];
				}
			}
		}

		throw new \BadMethodCallException("Inaccessible method '{$name}'");
	}

	public function __set($name, $value) {
		if (in_array($name, $this->columns)) {
			if (!$this->isPrimaryKey($name)) {
				$setter = 'set' . ucfirst($name);
				$this->{$setter}($value);
			}
		} else {
			$this->data[$name] = $value;
		}
	}

	public function __get($name) {
		if (in_array($name, $this->columns)) {
			$getter = 'get' . ucfirst($name);
			return $this->{$getter}();
		} else if (isset($this->data[$name])) {
			return $this->data[$name];
		}
		throw new \OutOfBoundsException("Parameter '{$name}' not set.");
	}

	public function __isset($name) {
		return $this->hasColumn($name) || isset($this->data[$name]);
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

class NoPrimaryKeyException {
	
}
