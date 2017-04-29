if ($my->vlogin) {
	if (!isset($my->cnpms) || (isset($my->cnpms) && $my->cnpms > 0)) {
		$result = $db->execute("
			SELECT p.id, p.topic, u.id AS pm_from, u.name
			FROM {$db->pre}pm AS p
				LEFT JOIN {$db->pre}user AS u ON u.id = p.pm_from
			WHERE p.pm_to = '{$my->id}' AND p.status = '0'
			ORDER BY p.date DESC
		");
		$pmcache = array();
		
		while ($row = $result->fetch()) {
			if (empty($row['name'])) {
				$row['name'] = $lang->phrase('fallback_no_username');
			}
			$pmcache[] = $row;
		}
		$my->cnpms = count($pmcache);
	}
	
	if ($my->cnpms > 0) {
		$tpl->assignVars(compact("pmcache"));
		echo $tpl->parse("modules/{$pluginid}/new_pms");
	}
}