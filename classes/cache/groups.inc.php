<?php
class cache_groups extends CacheItem {

	function load() {
		global $db;
		if ($this->exists() == true) {
			$this->import();
		}
		else {
			$fields = unserialize(file_get_contents('data/group_fields.php'));
			$fields = array_merge($fields['gFields'], $fields['maxFields'], $fields['minFields']);
			$keys = array_combine($fields, range(1, count($fields)));
			
			$result = $db->query('SELECT * FROM '.$db->pre.'groups', __LINE__, __FILE__);
			$this->data = array();
			$this->data['groupstandard'] = $this->data['team_ag'] = $this->data['team_ag']['admin'] = $this->data['team_ag']['gmod'] = $this->data['group_status'] = array();
			while ($row = $db->fetch_assoc($result)) {
				// groups
				$this->data['groups'][$row['id']] = array_intersect_key($row, $keys);
				
				// groupstandard
			   	if ($row['core'] == '1' && $row['guest'] == '1') {
					$this->data['groupstandard']['group_guest'] = $row['id'];
				}
				if ($row['core'] == '1' && $row['guest'] == '0' && $row['admin'] != '1') {
					$this->data['groupstandard']['group_member'] = $row['id'];
				}
				
				// group_status
				$this->data['group_status'][$row['id']] = array(
					'admin' => $row['admin'],
					'guest' => $row['guest'],
					'title' => $row['title'],
					'core' => $row['core']
				);
				
				// team_ag
		    	if ($row['admin'] == 1) {
		        	$this->data['team_ag']['admin'][] = $row['id'];
		        }
		        elseif ($row['gmod'] == 1) {
		        	$this->data['team_ag']['gmod'][] = $row['id'];
		        }
			}
			$this->export();
		}
	}

	function groups() {
		if ($this->data == null) {
			$this->load();
		}
		return $this->data['groups'];
	}
	function standard() {
		if ($this->data == null) {
			$this->load();
		}
		return $this->data['groupstandard'];
	}
	function status() {
		if ($this->data == null) {
			$this->load();
		}
		return $this->data['group_status'];
	}
	function team() {
		if ($this->data == null) {
			$this->load();
		}
		return $this->data['team_ag'];
	}

}
?>