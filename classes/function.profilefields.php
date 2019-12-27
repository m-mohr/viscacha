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

function admin_customsave($uid) {
	global $db, $gpc;
	$upquery = array();
	$query = $db->query("SELECT * FROM {$db->pre}profilefields");
	while($profilefield = $db->fetch_assoc($query)) {
		$thing = explode("\n", $profilefield['type'], 2);
		$type = $thing[0];
		$field = "fid{$profilefield['fid']}";

		$gpc_type = iif($type == 'multiselect' || $type == 'checkbox', arr_str, str);
		$value = $gpc->get($field, $gpc_type);

		if(($type == "multiselect" || $type == "checkbox") && is_array($value)) {
			$options = implode("\n", $value);
		}
		else {
			$options = $value;
		}
		$upquery[$field] = "{$field} = '{$options}'";
	}

	if (count($upquery) > 0) {
		$query = $db->query("SELECT * FROM {$db->pre}userfields WHERE ufid = '{$uid}'");
		$upquery['ufid'] = "ufid = '{$uid}'";
		$sqldata = implode(', ', $upquery);
		if($db->num_rows($query) == 0) {
			$db->query("INSERT INTO {$db->pre}userfields SET {$sqldata}");
		}
		else {
			$db->query("UPDATE {$db->pre}userfields SET {$sqldata} WHERE ufid = '{$uid}' LIMIT 1");
		}
	}
}

function admin_customfields($uid) {
	global $db, $gpc;
	$customfields = array('0' => array(), '1' => array(), '2' => array());
	$query = $db->query("SELECT * FROM ".$db->pre."userfields WHERE ufid = '{$uid}' LIMIT 1");
	$saved = $db->fetch_assoc($query);
	$query = $db->query("SELECT * FROM ".$db->pre."profilefields ORDER BY disporder");
	while($profilefield = $db->fetch_assoc($query)) {
		$select = '';
		$profilefield['type'] = $gpc->prepare($profilefield['type']);
		$thing = explode("\n", $profilefield['type'], 2);
		$type = $thing[0];
		if (!isset($thing[1])) {
			$options = '';
		}
		else {
			$options = $thing[1];
		}
		$field = "fid{$profilefield['fid']}";
		if($type == "multiselect") {
			$useropts = @explode("\n", $saved[$field]);
			while(list($key, $val) = each($useropts)) {
				$seloptions[$val] = $val;
			}
			$expoptions = explode("\n", $options);
			if(is_array($expoptions)) {
				while(list($key, $val) = each($expoptions)) {
					list($key, $val) = explode('=', $val, 2);
					$val = str_replace("\n", "\\n", trim($val));
					$select .= "<option value=\"{$key}\"".iif(isset($seloptions[$key]) && $key == $seloptions[$key], ' selected="selected"').">{$val}</option>";
				}
				if(!$profilefield['length']) {
					$profilefield['length'] = 3;
				}
				$code = "<select id=\"{$field}\" class=\"label\" name=\"{$field}[]\" size=\"{$profilefield['length']}\" multiple=\"multiple\">{$select}</select>";
			}
		}
		elseif($type == "select") {
			$expoptions = explode("\n", $options);
			if(is_array($expoptions)) {
				while(list($key, $val) = each($expoptions)) {
					list($key, $val) = explode('=', $val, 2);
					$val = str_replace("\n", "\\n", trim($val));
					$select .= "<option value=\"{$key}\"".iif($key == $saved[$field], ' selected="selected"').">{$val}</option>";
				}
				if(!$profilefield['length']) {
					$profilefield['length'] = 1;
				}
				$code = "<select id=\"{$field}\" class=\"label\" name=\"{$field}\" size=\"{$profilefield['length']}\">{$select}</select>";
			}
		}
		elseif($type == "radio") {
			$expoptions = explode("\n", $options);
			if(is_array($expoptions)) {
				while(list($key, $val) = each($expoptions)) {
					list($key, $val) = explode('=', $val, 2);
					$select .= "<input type=\"radio\" name=\"{$field}\" value=\"{$key}\"".iif($key == $saved[$field], ' checked="checked"')." /> {$val}<br />";
				}
				$code = '<div id="'.$field.'" class="label">'.$select.'</div>';
			}
		}
		elseif($type == "checkbox") {
			$useropts = @explode("\n", $saved[$field]);
			while(list($key, $val) = each($useropts)) {
				$seloptions[$val] = $val;
			}
			$expoptions = explode("\n", $options);
			if(is_array($expoptions)) {
				while(list($key, $val) = each($expoptions)) {
					list($key, $val) = explode('=', $val, 2);
					$select .= "<input type=\"checkbox\" name=\"{$field}[]\" value=\"{$key}\"".iif(isset($seloptions[$key]) && $key == $seloptions[$key], ' checked="checked"')." /> {$val}<br />";
				}
				$code = '<div id="'.$field.'" class="label">'.$select.'</div>';
			}
		}
		elseif($type == "textarea") {
			$value = $gpc->prepare($saved[$field]);
			$code = "<textarea id=\"{$field}\" class=\"label\" name=\"{$field}\" rows=\"5\" cols=\"40\">{$value}</textarea>";
		}
		else {
			$value = $gpc->prepare($saved[$field]);
			$code = "<input id=\"{$field}\" class=\"label\" type=\"text\" name=\"{$field}\" size=\"{$profilefield['length']}\"".iif($profilefield['maxlength'] > 0, "maxlength=\"{$profilefield['maxlength']}\"")." value=\"{$value}\" />";
		}
		$customfields[$profilefield['editable']][] = array(
			'input' => $code,
			'name' => $profilefield['name'],
			'description' => $profilefield['description'],
			'maxlength' => $profilefield['maxlength'],
			'field' => $field
		);
		unset($code, $select, $val, $options, $expoptions, $useropts, $seloptions);
	}
	return $customfields;
}

function addprofile_customprepare($e1 = 'error_missingrequiredfield', $e2 = 'error_customfieldtoolong') {
	global $db, $gpc, $lang;
	$error = array();
	$upquery = array();
	$query = $db->query("SELECT * FROM {$db->pre}profilefields WHERE editable != '0' AND required = '1' ORDER BY disporder");
	while($profilefield = $db->fetch_assoc($query)) {
		$profilefield['type'] = $gpc->prepare($profilefield['type']);
		$thing = explode("\n", $profilefield['type'], 2);
		$type = $thing[0];
		$field = "fid{$profilefield['fid']}";

		$gpc_type = iif($type == 'multiselect' || $type == 'checkbox', arr_str, str);
		$value = $gpc->get($field, $gpc_type);

		if($profilefield['required'] == 1 && (empty($value) || (is_array($value) && count($value) == 0))) {
			$error[] = $lang->phrase($e1);
		}
		if($profilefield['maxlength'] > 0 && ((is_string($value) && strxlen($value) > $profilefield['maxlength']) || (is_array($value) && count($value) > $profilefield['maxlength']))) {
			$error[] = $lang->phrase($e2);
		}

		if($type == "multiselect" || $type == "checkbox") {
			if (is_array($value)) {
				$upquery[$field] = implode("\n", $value);
			}
			else {
				$upquery[$field] = '';
			}
		}
		else {
			$upquery[$field] = $value;
		}
	}
	return array(
		'data' => $upquery,
		'error' => $error
	);
}

function addprofile_customsave($data, $uid) {
	global $db;
	if (count($data) > 0) {
		$fields = $db->list_fields("{$db->pre}userfields");
		$sqldata = array();
		foreach ($fields as $field) {
			if (isset($data[$field])) {
				$sqldata[$field] = "'{$data[$field]}'";
			}
			else {
				$sqldata[$field] = "''";
			}
		}
		$sqldata['ufid'] = "'{$uid}'";
		$fields = implode(', ', $fields);
		$sqldata = implode(', ', $sqldata);
		$db->query("INSERT INTO {$db->pre}userfields ({$fields}) VALUES ({$sqldata})");
	}
}

function addprofile_customfields($data = array()) {
	global $db, $gpc;
	$customfields = array();
	$query = $db->query("SELECT * FROM {$db->pre}profilefields WHERE required = '1' AND editable != '0' ORDER BY disporder");
	while($profilefield = $db->fetch_assoc($query)) {
		$select = '';
		$profilefield['type'] = $gpc->prepare($profilefield['type']);
		$thing = explode("\n", $profilefield['type'], 2);
		$type = $thing[0];
		if (!isset($thing[1])) {
			$options = '';
		}
		else {
			$options = $thing[1];
		}
		$field = "fid{$profilefield['fid']}";
		if($type == "multiselect") {
			$expoptions = explode("\n", $options);
			if(is_array($expoptions)) {
				while(list($key, $val) = each($expoptions)) {
					list($key, $val) = explode('=', $val, 2);
					if (isset($data[$field]) && $data[$field] == $val) {
						$selected = ' selected="selected"';
					}
					else {
						$selected = '';
					}
					$val = str_replace("\n", "\\n", trim($val));
					$select .= "<option value=\"{$key}\"{$selected}>{$val}</option>";
				}
				if(!$profilefield['length']) {
					$profilefield['length'] = 3;
				}
				$code = "<select id=\"{$field}\" class=\"label\" name=\"{$field}[]\" size=\"{$profilefield['length']}\" multiple=\"multiple\">{$select}</select>";
			}
		}
		elseif($type == "select") {
			$expoptions = explode("\n", $options);
			if(is_array($expoptions)) {
				while(list($key, $val) = each($expoptions)) {
					list($key, $val) = explode('=', $val, 2);
					if (isset($data[$field]) && $data[$field] == $val) {
						$selected = ' selected="selected"';
					}
					else {
						$selected = '';
					}
					$val = str_replace("\n", "\\n", trim($val));
					$select .= "<option value=\"{$key}\"{$selected}>{$val}</option>";
				}
				if(!$profilefield['length']) {
					$profilefield['length'] = 1;
				}
				$code = "<select id=\"{$field}\" class=\"label\" name=\"{$field}\" size=\"{$profilefield['length']}\">{$select}</select>";
			}
		}
		elseif($type == "radio") {
			$expoptions = explode("\n", $options);
			if(is_array($expoptions)) {
				while(list($key, $val) = each($expoptions)) {
					list($key, $val) = explode('=', $val, 2);
					if (isset($data[$field]) && $data[$field] == $val) {
						$checked = ' checked="checked"';
					}
					else {
						$checked = '';
					}
					$select .= "<input type=\"radio\" name=\"{$field}\" value=\"{$key}\"{$checked} /> {$val}<br />";
				}
				$code = '<div id="'.$field.'" class="label">'.$select.'</div>';
			}
		}
		elseif($type == "checkbox") {
			$expoptions = explode("\n", $options);
			if(is_array($expoptions)) {
				while(list($key, $val) = each($expoptions)) {
					list($key, $val) = explode('=', $val, 2);
					if (isset($data[$field]) && $data[$field] == $val) {
						$checked = ' checked="checked"';
					}
					else {
						$checked = '';
					}
					$select .= "<input type=\"checkbox\" name=\"{$field}[]\" value=\"{$key}\"{$checked} /> {$val}<br />";
				}
				$code = '<div id="'.$field.'" class="label">'.$select.'</div>';
			}
		}
		elseif($type == "textarea") {
			if (!empty($data[$field])) {
				$content = $gpc->prepare($data[$field]);
			}
			else {
				$content = '';
			}
			$code = "<textarea id=\"{$field}\" class=\"label\" name=\"{$field}\" rows=\"5\" cols=\"40\">{$content}</textarea>";
		}
		else {
			$code = "<input id=\"{$field}\" class=\"label\" type=\"text\" name=\"{$field}\" size=\"{$profilefield['length']}\"".iif($profilefield['maxlength'] > 0, "maxlength=\"{$profilefield['maxlength']}\"")." />";
		}
		$customfields[] = array(
			'input' => $code,
			'name' => $profilefield['name'],
			'description' => $profilefield['description'],
			'maxlength' => $profilefield['maxlength'],
			'field' => $field
		);
		unset($code, $select, $val, $options, $expoptions, $useropts, $seloptions);
	}
	return $customfields;
}

function editprofile_customsave($editable, $uid, $save = true) {
	global $db, $lang, $gpc;
	$error = array();
	$upquery = array();
	$query = $db->query("SELECT * FROM {$db->pre}profilefields WHERE editable = '{$editable}' ORDER BY disporder");
	while($profilefield = $db->fetch_assoc($query)) {
		$thing = explode("\n", $profilefield['type'], 2);
		$type = $thing[0];
		$field = "fid{$profilefield['fid']}";

		$gpc_type = iif($type == 'multiselect' || $type == 'checkbox', arr_str, str);
		$value = $gpc->get($field, $gpc_type);

		if($profilefield['required'] == 1 && (empty($value) || (is_array($value) && count($value) == 0))) {
			$error[] = $lang->phrase('error_missingrequiredfield');
		}
		if($profilefield['maxlength'] > 0 && ((is_string($value) && strxlen($value) > $profilefield['maxlength']) || (is_array($value) && count($value) > $profilefield['maxlength']))) {
			$error[] = $lang->phrase('error_customfieldtoolong');
		}

		if(($type == "multiselect" || $type == "checkbox") && is_array($value)) {
			if (is_array($value)) {
				$upquery[$field] = implode("\n", $value);
			}
			else {
				$upquery[$field] = '';
			}
		}
		else {
			$upquery[$field] = $value;
		}
	}

	if (count($error) == 0 && count($upquery) > 0 && $save == true) {
		$query = $db->query("SELECT * FROM {$db->pre}userfields WHERE ufid='{$uid}'");
		if($db->num_rows($query) == 0) {
			$fields = $db->list_fields("{$db->pre}userfields");
			$sqldata = array();
			foreach ($fields as $field) {
				if (isset($upquery[$field])) {
					$sqldata[$field] = "'{$upquery[$field]}'";
				}
				else {
					$sqldata[$field] = "''";
				}
			}
			$sqldata['ufid'] = "'{$uid}'";
			$fields = implode(', ', $fields);
			$sqldata = implode(', ', $sqldata);
			$db->query("INSERT INTO {$db->pre}userfields ({$fields}) VALUES ({$sqldata})");
		}
		else {
			$sqldata = array();
			foreach ($upquery as $field => $value) {
				$sqldata[] = "{$field} = '{$value}'";
			}
			$sqldata = implode(', ', $sqldata);
			$db->query("UPDATE {$db->pre}userfields SET {$sqldata} WHERE ufid = '{$uid}' LIMIT 1");
		}
	}

	return $error;
}

function editprofile_customfields($editable, $uid) {
	global $db, $gpc;
	$customfields = array();
	$query = $db->query("SELECT * FROM ".$db->pre."userfields WHERE ufid = '{$uid}' LIMIT 1");
	$saved = $db->fetch_assoc($query);
	$query = $db->query("SELECT * FROM ".$db->pre."profilefields WHERE editable = '{$editable}' ORDER BY disporder");
	while($profilefield = $db->fetch_assoc($query)) {
		$select = '';
		$profilefield['type'] = $gpc->prepare($profilefield['type']);
		$thing = explode("\n", $profilefield['type'], 2);
		$type = $thing[0];
		if (!isset($thing[1])) {
			$options = '';
		}
		else {
			$options = $thing[1];
		}
		$field = "fid{$profilefield['fid']}";
		if($type == "multiselect") {
			$useropts = @explode("\n", $saved[$field]);
			while(list($key, $val) = each($useropts)) {
				$seloptions[$val] = $val;
			}
			$expoptions = explode("\n", $options);
			if(is_array($expoptions)) {
				while(list($key, $val) = each($expoptions)) {
					list($key, $val) = explode('=', $val, 2);
					$val = str_replace("\n", "\\n", trim($val));
					$select .= "<option value=\"{$key}\"".iif(isset($seloptions[$key]) && $key == $seloptions[$key], ' selected="selected"').">{$val}</option>";
				}
				if(!$profilefield['length']) {
					$profilefield['length'] = 3;
				}
				$code = "<select id=\"{$field}\" class=\"label\" name=\"{$field}[]\" size=\"{$profilefield['length']}\" multiple=\"multiple\">{$select}</select>";
			}
		}
		elseif($type == "select") {
			$expoptions = explode("\n", $options);
			if(is_array($expoptions)) {
				while(list($key, $val) = each($expoptions)) {
					list($key, $val) = explode('=', $val, 2);
					$val = str_replace("\n", "\\n", trim($val));
					$select .= "<option value=\"{$key}\"".iif($key == $saved[$field], ' selected="selected"').">{$val}</option>";
				}
				if(!$profilefield['length']) {
					$profilefield['length'] = 1;
				}
				$code = "<select id=\"{$field}\" class=\"label\" name=\"{$field}\" size=\"{$profilefield['length']}\">{$select}</select>";
			}
		}
		elseif($type == "radio") {
			$expoptions = explode("\n", $options);
			if(is_array($expoptions)) {
				while(list($key, $val) = each($expoptions)) {
					list($key, $val) = explode('=', $val, 2);
					$select .= "<input type=\"radio\" name=\"{$field}\" value=\"{$key}\"".iif($key == $saved[$field], ' checked="checked"')." /> {$val}<br />";
				}
				$code = '<div id="'.$field.'" class="label">'.$select.'</div>';
			}
		}
		elseif($type == "checkbox") {
			$useropts = @explode("\n", $saved[$field]);
			while(list($key, $val) = each($useropts)) {
				$seloptions[$val] = $val;
			}
			$expoptions = explode("\n", $options);
			if(is_array($expoptions)) {
				while(list($key, $val) = each($expoptions)) {
					list($key, $val) = explode('=', $val, 2);
					$select .= "<input type=\"checkbox\" name=\"{$field}[]\" value=\"{$key}\"".iif(isset($seloptions[$key]) && $key == $seloptions[$key], ' checked="checked"')." /> {$val}<br />";
				}
				$code = '<div id="'.$field.'" class="label">'.$select.'</div>';
			}
		}
		elseif($type == "textarea") {
			$value = $gpc->prepare($saved[$field]);
			$code = "<textarea id=\"{$field}\" class=\"label\" name=\"{$field}\" rows=\"5\" cols=\"40\">{$value}</textarea>";
		}
		else {
			$value = $gpc->prepare($saved[$field]);
			$code = "<input id=\"{$field}\" class=\"label\" type=\"text\" name=\"{$field}\" size=\"{$profilefield['length']}\"".iif($profilefield['maxlength'] > 0, "maxlength=\"{$profilefield['maxlength']}\"")." value=\"{$value}\" />";
		}
		$customfields[] = array(
			'input' => $code,
			'name' => $profilefield['name'],
			'description' => $profilefield['description'],
			'maxlength' => $profilefield['maxlength'],
			'field' => $field
		);
		unset($code, $select, $val, $options, $expoptions, $useropts, $seloptions);
	}
	return $customfields;
}
?>
