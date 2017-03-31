if ($my->vlogin) {
	if (!isset($my->cnpms) || (isset($my->cnpms) && $my->cnpms > 0)) {
		$result = $db->query("
			SELECT p.id, p.topic, u.id AS pm_from, u.name
			FROM {$db->pre}pm AS p
				LEFT JOIN {$db->pre}user AS u ON u.id = p.pm_from
			WHERE p.pm_to = '{$my->id}' AND p.status = '0'
			ORDER BY p.date DESC
		");
		$my->cnpms = $db->num_rows($result);
	}
	
	if ($my->cnpms > 0) {
		$pmcache = array();
		
		while ($row = $db->fetch_assoc($result)) {
			if (empty($row['name'])) {
				$row['name'] = $lang->phrase('fallback_no_username');
			}
			$row['topic'] = $gpc->prepare($row['topic']);
			$pmcache[] = $row;
		}
		$tpl->assignVars(compact("pmcache"));
		echo $tpl->parse("modules/{$pluginid}/new_pms");
	}
}