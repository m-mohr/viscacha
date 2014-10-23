<?php
/*
	Viscacha - A bulletin board solution for easily managing your content
	Copyright (C) 2004-2009  The Viscacha Project

	Author: Matthias Mohr (et al.)
	Publisher: The Viscacha Project, http://www.viscacha.org
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

if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

class ProfileFieldViewer {

	var $settings;
	var $data;
	var $cache;
	var $uid;

	/**
	 * Constructs a new ProfileFieldViewer.
	 *
	 * @param int $uid
	 */
	function ProfileFieldViewer($uid = null) {
		$this->settings = null;
		$this->data = array();
		$this->cache = array();
		if ($uid == null) {
			$this->uid = 0;
		}
		else {
			$this->uid = $uid;
		}
	}

	/**
	 * Loads the profilefield settings.
	 *
	 * The data is only loaded once.
	 *
	 * @access private
	 */
	function loadSettingData() {
		if ($this->settings == null) {
			global $db;
			$this->settings = array();
			$query = $db->query("SELECT * FROM {$db->pre}profilefields ORDER BY disporder");
			while($row = $db->fetch_assoc($query)) {
				$thing = explode("\n", $row['type'], 2);
				$row['type'] = $thing[0];
				if (empty($thing[1])) {
					$row['options'] = '';
				}
				else {
					$row['options'] = $thing[1];
				}
				$this->settings[$row['fid']] = $row;
			}
		}
	}

	/**
	 * Set the current handled user.
	 *
	 * Set this to 0 to disable the methods related to the functions dealing with values:
	 * loadUserData(), setUserData(), getFieldValue(), getAll().
	 * This functions return empty values then.
	 *
	 * @param int $uid
	 */
	function setUserId($uid) {
		$this->uid = $uid;
	}

	/**
	 * Loads user data from database.
	 *
	 * You have to specify the user id before.
	 */
	function loadUserData() {
		if (!isset($this->data[$this->uid]) && $this->uid > 0) {
			global $db, $gpc;
			$this->cache[$this->uid] = array();
			$this->data[$this->uid] = array();
			$result = $db->query("SELECT * FROM {$db->pre}userfields WHERE ufid = '{$this->uid}'");
			$row = $db->fetch_assoc($result);
			unset($row['ufid']);
			$row = $gpc->prepare($row);
			$this->data[$this->uid] = $row;
		}
	}

	/**
	 * Set user data by giving an array or an object.
	 *
	 * You have to specify the user id before.
	 *
	 * @param mixed $data
	 */
	function setUserData($data) {
		if ($this->uid > 0) {
			$this->cache[$this->uid] = array();
			$this->data[$this->uid] = array();
			if (is_array($data)) {
				foreach ($data as $key => $value) {
					if (substr($key, 0, 3) == 'fid') {
						$key = substr($key, 3);
						$this->data[$this->uid][$key] = $value;
					}
				}
			}
			elseif (is_object($data)) {
				$ovar = get_object_vars($data);
				foreach ($ovar as $key => $value) {
					if (substr($key, 0, 3) == 'fid') {
						$key = substr($key, 3);
						$this->data[$this->uid][$key] = $value;
					}
				}
			}
		}
	}

	/**
	 * Gets the value of the specified field id for the current user.
	 *
	 * You have to specify the user id before.
	 *
	 * @param int $fid
	 */
	function getFieldValue($fid) {
		if ($this->uid > 0) {
			$this->loadUserData();
			$row = $this->prepareData($fid);
			return $row['value'];
		}
		else {
			return '';
		}
	}

	/**
	 * Gets the name of the specified field id.
	 *
	 * @param int $fid
	 */
	function getFieldName($fid) {
		$this->loadSettingData();
		return $this->settings[$fid]['name'];
	}

	/**
	 * Prepares the data for given field id and user id.
	 *
	 * You have to specify the user id before.
	 *
	 * @param int $fid
	 * @param int $uid
	 * @return array
	 * @access private
	 */
	function prepareData($fid) {
		if (!isset($this->cache[$this->uid][$fid]) && $this->uid > 0) {
			$this->loadSettingData();
			$profilefield = $this->settings[$fid];
			$fielddata = $this->data[$this->uid][$fid];

			if($profilefield['type'] == "multiselect") {
				$useropts = @explode("\n", $fielddata);
				while(list($key, $val) = each($useropts)) {
					$seloptions[$val] = $val;
				}
				$expoptions = explode("\n", $profilefield['options']);
				if(is_array($expoptions)) {
					$select = array();
					while(list($key, $val) = each($expoptions)) {
						list($key, $val) = explode('=', $val, 2);
						if(isset($seloptions[$key]) && $key == $seloptions[$key]) {
							$select[] = trim($val);
						}
					}
					$code = implode(', ', $select);
				}
			}
			elseif($profilefield['type'] == "select") {
				$expoptions = explode("\n", $profilefield['options']);
				if(is_array($expoptions)) {
					while(list($key, $val) = each($expoptions)) {
						list($key, $val) = explode('=', $val, 2);
						if ($key == $fielddata) {
							$code = trim($val);
						}
					}
				}
			}
			elseif($profilefield['type'] == "radio") {
				$expoptions = explode("\n", $profilefield['options']);
				if(is_array($expoptions)) {
					while(list($key, $val) = each($expoptions)) {
						list($key, $val) = explode('=', $val, 2);
						if ($key == $fielddata) {
							$code = trim($val);
						}
					}
				}
			}
			elseif($profilefield['type'] == "checkbox") {
				$useropts = @explode("\n", $fielddata);
				while(list($key, $val) = each($useropts)) {
					$seloptions[$val] = $val;
				}
				$expoptions = explode("\n", $profilefield['options']);
				if(is_array($expoptions)) {
					$select = array();
					while(list($key, $val) = each($expoptions)) {
						list($key, $val) = explode('=', $val, 2);
						if (isset($seloptions[$key]) && $key == $seloptions[$key]) {
							$select[] = trim($val);
						}
					}
					$code = implode(', ', $select);
				}
			}
			elseif($profilefield['type'] == "textarea") {
				$code = nl2br($fielddata);
			}
			else {
				$code = $fielddata;
			}
			if (empty($code)) {
				global $lang;
				$code = $lang->phrase('profile_na');
			}
			$this->cache[$this->uid][$fid] = array(
				'value' => $code,
				'name' => $profilefield['name'],
				'description' => $profilefield['description'],
				'maxlength' => $profilefield['maxlength'],
				'viewable' => $profilefield['viewable']
			);
		}
		if ($this->uid > 0) {
			return $this->cache[$this->uid][$fid];
		}
		else {
			return array();
		}
	}

	/**
	 * Gets all the data in an array, separated by the viewable positions.
	 *
	 * @return array
	 */
	function getAll() {
		$customfields = array('1' => array(), '2' => array(), '3' => array());
		if ($this->uid > 0) {
			$this->loadUserData();
			$keys = array_keys($this->data[$this->uid]);
			foreach ($keys as $fid) {
				$row = $this->prepareData($fid);
				if ($row['viewable'] != 0) {
					$customfields[$row['viewable']][] = array(
						'value' => $row['value'],
						'name' => $row['name'],
						'description' => $row['description'],
						'maxlength' => $row['maxlength']
					);
				}
			}
		}
		return $customfields;
	}
}
?>