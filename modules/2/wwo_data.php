class WWO_Cache {

	var $wwo;

	function WWO_Cache() {
		$this->wwo = null;
	}

	function get() {
		if ($this->wwo == null) {
			$this->load();
		}
		return $this->wwo;
	}
	
	function load() {
		global $db, $lang;
		
		$wwo = array(
			'i' => 0,
			'r' => 0,
			'g' => 0,
			'b' => 0,
			'list' => array()
		);
		
		$result = $db->query("
			SELECT s.mid, s.is_bot, u.name
			FROM {$db->pre}session AS s 
				LEFT JOIN {$db->pre}user AS u ON s.mid = u.id
			ORDER BY u.name
		",__LINE__,__FILE__);
		$count = $db->num_rows($result);
		$sep = $lang->phrase('listspacer');
		while ($row = $db->fetch_assoc($result)) {
			$wwo['i']++;
			if ($row['mid'] > 0) {
				$wwo['r']++;
				$row['sep'] = $sep;
				$wwo['list'][] = $row;
			}
			elseif ($row['is_bot'] > 0) {
				$wwo['b']++;
			}
			else {
				$wwo['g']++;
			}
		}
		
		if ($wwo['r'] > 0) {
			$wwo['list'][$wwo['r']-1]['sep'] = '';
		}
		
		$this->wwo = $wwo;
	}
}
$wwo_module = new WWO_Cache();