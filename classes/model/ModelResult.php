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
		
		// As we modify this array, but need it multiple times: Copy it
		$map = $this->columnMap;
		
		// Group data by alias
		$groups = array();
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
		foreach ($map as $column => $data) {
			$model = new $data['class']();
			$model->injectData($groups[$data['alias']], true);
			$map[$column]['model'] = $model;
		}
		
		// Cascade models
		ksort($map);
		$root = array_shift($map);
		$this->cascadeModels($map, $root['model'], $root['column']);
		return $root['model'];
	}
	
	private function cascadeModels(array $map, BaseModel $parentModel, $parentPath, $level = 1) {
        foreach($map as $column => $data) {
            $tree = explode('.', $column);
            if(!isset($tree[$level]) || strpos($column, $parentPath) !== 0) {
				continue;
			}

			$nextLevel = $level + 1;
			if (!isset($tree[$nextLevel])) {
				$parentModel->injectRelationData($tree[$level], $data['model']);
				$this->cascadeModels($map, $data['model'], $data['column'], $nextLevel);
			}
        }
	}
	
    public function __call($method, $args) {
        return call_user_func_array(array($this->result, $method), $args);
    }
	
}