$id = $config['viscacha_document_on_portal']['doc_id'];
$separator = $tpl->parse("modules/{$pluginid}/separator");

$result = $db->query("SELECT d.id, d.author, d.date, d.update, d.type, d.groups, c.lid, c.content, c.active, c.title FROM {$db->pre}documents AS d LEFT JOIN {$db->pre}documents_content AS c ON d.id = c.did WHERE d.id = '{$id}' ".iif($my->p['admin'] != 1, ' AND c.active = "1"'));
if ($db->num_rows($result) > 0) {

	$info = null;
	$data = array();
	while ($row = $db->fetch_assoc($result)) {
		if (!is_array($info)) {
			$info = array(
				'id' => $row['id'],
				'author' => $row['author'],
				'date' => $row['date'],
				'date2' => $row['date'],
				'update' => $row['update'],
				'update2' => $row['update'],
				'type' => $row['type'],
				'groups' => $row['groups'],
				'name' => null
			);
		}
		$data[$row['lid']] = array(
			'content' => $row['content'],
			'active' => $row['active'],
			'title' => $row['title']
		);
	}
	
	if (GroupCheck($info['groups'])) {
		$memberdata_obj = $scache->load('memberdata');
		$memberdata = $memberdata_obj->get();
	
		if(is_id($info['author']) && isset($memberdata[$info['author']])) {
			$info['name'] = $memberdata[$info['author']];
		}
		else {
			$info['name'] = $lang->phrase('fallback_no_username');
		}
		if ($info['date'] > 0 ) {
			$info['date'] = str_date($lang->phrase('dformat1'), times($info['date']));
		}
		else {
			$info['date'] = $lang->phrase('docs_date_na');
		}
		if ($info['update'] > 0) {
			$info['update'] = str_date($lang->phrase('dformat1'), times($info['update']));
		}
		else {
			$info['update'] = $lang->phrase('docs_date_na');
		}
	
		$type = doctypes();
		if (isset($type[$info['type']])) {
			$typedata = $type[$info['type']];
		}
		else {
			$typedata = array(
				'title' => 'Fallback',
				'template' => '',
				'parser' => 1,
				'inline' => 1,
				'remote' => 0
			);
		}

		$lid = getDocLangID($data);
		$info = array_merge($info, $data[$lid]);
	
		if ($typedata['inline'] == 0) {
			if ($typedata['remote'] == 0) {
				$info['content'] = DocCodeParser($info['content'], $typedata['parser']);
			}
			else {
				$info['file'] = $info['content'];
			}
			if (empty($typedata['template'])) {
				echo $info['content'].$separator;
			}
			else {
				echo $tpl->parse("docs/{$typedata['template']}").$separator;
			}
		}
		else {
			if (empty($typedata['template'])) {
				preg_match("~<title>(.+?)</title>~is", $info['content'], $match_title);
				preg_match("~<body[^>]*?>(.+?)</body>~is", $info['content'], $match_body);
	
				if (!empty($match_title[1])) {
					$info['title'] = $match_title[1];
				}
				if (!empty($match_body[1])) {
					$info['content'] = $match_body[1];
				}
				echo DocCodeParser($info['content'], $typedata['parser']).$separator;
			}
			else {
				$info['content'] = DocCodeParser($info['content'], $typedata['parser']);
				echo $tpl->parse("docs/{$typedata['template']}").$separator;
			}
	
		}
	}
}