<?php

namespace Viscacha\Model;

class ModelCollection implements \ArrayAccess, \Countable {

	protected $array;

	public function __construct(array $array = array()) {
		$this->array = $array;
	}

	public function count() {
		return count($this->array);
	}

	public function offsetExists($offset) {
		return isset($this->array[$offset]);
	}

	public function offsetGet($offset) {
		return $this->array[$offset];
	}

	public function offsetSet($offset, $value) {
		if (is_null($offset)) {
			$this->array[] = $value;
		} else {
			$this->array[$offset] = $value;
		}
	}

	public function offsetUnset($offset) {
		unset($this->array[$offset]);
	}

	public function toArray() {
		return $this->array;
	}

	public function merge($data) {
		if (is_array($data)) {
			$this->array = array_merge($this->array, $data);
		} else if ($data instanceof ModelCollection) {
			$this->merge($data->toArray());
		} else {
			throw new \InvalidArgumentException();
		}
	}
	
	public function load() {
		$count = 0;
		foreach($this->array as $model) {
			if ($model->load()) {
				$count++;
			}
		}
		return $count;
	}

	public function save() {
		$count = 0;
		foreach($this->array as $model) {
			if ($model->save()) {
				$count++;
			}
		}
		return $count;
	}

	public function __call($name, $arguments) {
		// This allows method chaining on arrays, a little like with jQuery.
		// It combines the results to a new collection.
		$collection = null;
		foreach ($this->array as $value) {
			$result = call_user_func_array(array($value, $name), $arguments);
			if (is_array($result)) {
				if ($collection === null) {
					$collection = new ModelCollection();
				}
				$collection->merge($result);
			}
		}
		return $collection ?: $this;
	}

}
