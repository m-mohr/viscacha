<?php
class cache_package_browser extends CacheItem {

	var $types;

	function cache_package_browser($filename, $cachedir = "cache/") {
		global $lang;
		if (SCRIPTNAME != 'admin') {
			$lang->group('admin/global');
		}
		parent::CacheItem($filename, $cachedir);
		$this->max_age = 60*60*6;
		$this->types = array(
		 	1 => array(
			 		'name' => $lang->phrase('admin_pb_type1_name'),
			 		'name2' => $lang->phrase('admin_pb_type1_name2'),
			 		'import' => 'admin.php?action=packages&job=package_import&file=',
			 		'update' => 'admin.php?action=packages&job=package_update&file='
		 		),
		 	2 => array(
			 		'name' => $lang->phrase('admin_pb_type2_name'),
			 		'name2' => $lang->phrase('admin_pb_type2_name2'),
					'import' => 'admin.php?action=designs&job=design_import&file=',
					'update' => null
		 		),
		 	3 => array(
			 		'name' => $lang->phrase('admin_pb_type3_name'),
			 		'name2' => $lang->phrase('admin_pb_type3_name2'),
					'import' => 'admin.php?action=bbcodes&job=smileys_import&file=',
					'update' => null
		 		),
		 	4 => array(
			 		'name' => $lang->phrase('admin_pb_type4_name'),
			 		'name2' => $lang->phrase('admin_pb_type4_name2'),
					'import' => 'admin.php?action=language&job=import&file=',
					'update' => null
		 		),
		 	5 => array(
			 		'name' => $lang->phrase('admin_pb_type5_name'),
			 		'name2' => $lang->phrase('admin_pb_type5_name2'),
					'import' => 'admin.php?action=bbcodes&job=custombb_import&file=',
					'update' => null
		 		),
		);
	}

	function load () {
		global $config;
		if ($this->exists() == true) {
			$this->import();
		}
		else {
			global $config, $admconfig;
			$this->data = array();
			$myini = new INI();
			$servers = explode(';', $admconfig['package_server']);
			if (is_array($servers)) {
				foreach ($servers as $server) {
					$content = get_remote($server.'/external.ini');
					if ($content != REMOTE_CLIENT_ERROR) {
						$inis = $myini->parse($content);
						if (!isset($inis['files'])) {
							break;
						}
						foreach ($inis['files'] as $type => $remotefile) {
							if (!isset($this->data[$type])) {
								$this->data[$type] = array();
							}
							if (empty($remotefile)) {
								continue;
							}
							$path = $server.'/'.$remotefile;
							$content = get_remote($path);
							if ($content != REMOTE_CLIENT_ERROR) {
								$new_data = $myini->parse($content);
								if (!isset($new_data['categories'])) {
									continue;
								}
								foreach ($new_data['categories'] as $cid => $cname) {
									$this->data[$type]['categories'][$cid] = array(
										'name' => $cname,
										'entries' => 0
									);
									$this->data[$type][$cid] = array();
								}
								foreach ($new_data as $key => $row) {
									if ($key == 'categories') {
										continue;
									}
									else {
										if (!isset($row['category']) || !isset($this->data[$type]['categories'][$row['category']])) {
											continue;
										}
										if (( isset($this->data[$type][$row['internal']]) &&
												version_compare($this->data[$type][$this->data[$type][$row['internal']]][$row['internal']]['version'], $row['version'], ">") ) ||
												!isset($this->data[$type][$row['internal']])
											) {
											$row['server'] = $server;
											$this->data[$type]['categories'][$row['category']]['entries']++;
											$this->data[$type][$row['category']][$row['internal']] = $row;
											$this->data[$type][$row['internal']] = $row['category'];
										}
									}
								}
							}
						}
					}
				}
			}
			$this->export();
		}
	}

	function types($type = null) {
		if ($type != null && isset($this->types[$type])) {
			return $this->types[$type];
		}
		else {
			return $this->types;
		}
	}

	function getOne($type = IMPTYPE_PACKAGE, $id) {
		if ($this->data == null || $this->expired($this->max_age)) {
			$this->load();
		}
		if (isset($this->data[$type][$id]) && isset($this->data[$type][$this->data[$type][$id]][$id])) {
			return $this->data[$type][$this->data[$type][$id]][$id];
		}
		else {
			return false;
		}
	}

	function categories($type = IMPTYPE_PACKAGE, $id = null) {
		if ($this->data == null || $this->expired($this->max_age)) {
			$this->load();
		}
		if ($id == null) {
			return isset($this->data[$type]['categories']) ? $this->data[$type]['categories'] : array();
		}
		else {
			return isset($this->data[$type]['categories'][$id]) ? $this->data[$type]['categories'][$id] : array();
		}
	}

	function get ($type = IMPTYPE_PACKAGE, $category = null) {
		if ($this->data == null || $this->expired($this->max_age)) {
			$this->load();
		}
		if ($category == null) {
			$ret = isset($this->data[$type]) ? $this->data[$type] : array();
		}
		else {
			$ret = isset($this->data[$type][$category]) ? $this->data[$type][$category] : array();
		}
		return $ret;
	}
}
?>