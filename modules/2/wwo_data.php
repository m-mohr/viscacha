class WWO_Cache {

	var $wwo;

	function __construct() {
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
			'list' => array()
		);
		
		$result = $db->execute("
			SELECT s.mid, u.name
			FROM {$db->pre}session AS s 
				LEFT JOIN {$db->pre}user AS u ON s.mid = u.id
			ORDER BY u.name
		");
		$sep = $lang->phrase('listspacer');
		while ($row = $result->fetch()) {
			$wwo['i']++;
			if ($row['mid'] > 0) {
				$wwo['r']++;
				$row['sep'] = $sep;
				$wwo['list'][] = $row;
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