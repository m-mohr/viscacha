$id = $config['viscacha_document_on_portal']['doc_id'];

$result = $db->query("
	SELECT d.id, u.id AS author, u.name, d.date, d.update, d.date AS date2, d.update AS update2, d.parser, d.template, d.groups
	FROM {$db->pre}documents AS d
		LEFT JOIN {$db->pre}user AS u ON u.id = d.author
	WHERE d.id = '{$id}'
");
if ($db->num_rows($result) == 0) {
	return;
}
$info = $db->fetch_assoc($result);

$result2 = $db->query("SELECT * FROM  {$db->pre}documents_content WHERE did = '{$id}'");
$data = array();
while ($row = $db->fetch_assoc($result2)) {
	$data[$row['lid']] = $row;
}

if (!GroupCheck($info['groups'])) {
	return;
}

$lid = getDocLangID($data);
$document = $data[$lid];

if(empty($info['name'])) {
	$info['name'] = $lang->phrase('fallback_no_username');
}
if ($info['date'] > 0 ) {
	$info['date'] = date($lang->phrase('datetime_format'), times($info['date']));
}
else {
	$info['date'] = $lang->phrase('docs_date_na');
}
if ($info['update'] > 0) {
	$info['update'] = date($lang->phrase('datetime_format'), times($info['update']));
}
else {
	$info['update'] = $lang->phrase('docs_date_na');
}
	
$document['content'] = DocCodeParser($document['content'], $info['parser']);

echo $tpl->parse("docs/{$info['template']}");