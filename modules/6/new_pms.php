if ($my->vlogin) {
	if (!isset($my->cnpms) || (isset($my->cnpms) && $my->cnpms > 0)) {
		$result = $db->query("SELECT id, topic, pm_from FROM {$db->pre}pm AS p WHERE pm_to = '{$my->id}' AND status = '0' ORDER BY date DESC");
		$my->cnpms = $db->num_rows($result);
	}
	
	if ($my->cnpms > 0) {
		$memberdata_obj = $scache->load('memberdata');
		$memberdata = $memberdata_obj->get();
		$pmcache = array();
		
		while ($row = $db->fetch_assoc($result)) {
			if (isset($memberdata[$row['pm_from']])) {
				$row['name'] = $memberdata[$row['pm_from']];
			}
			else {
				$row['name'] = $lang->phrase('fallback_no_username');
			}
			$row['topic'] = $gpc->prepare($row['topic']);
			$pmcache[] = $row;
		}
		$tpl->globalvars(compact("pmcache"));
		echo $tpl->parse("modules/{$pluginid}/new_pms");
	}
}