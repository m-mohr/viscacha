<?php

namespace Viscacha\Model;

class UserSelf extends User {

	private $newPm = null;

	public function newPm() {
		if (is_array($this->newPm)) {
			return $this->newPm;
		}

		$result = Pm::select()->with(['pm_from' => 'author'])
						->where('pm_to', $this->id)->onlyNew()
						->sortDesc('pm.date')->execute()->fetchObject();

		$this->newPm = array($result);

		return $this->newPm;
	}

	public function newPmCount() {
		if (!is_array($this->newPm)) {
			$this->newPm();
		}

		return count($this->newPm);
	}

}
