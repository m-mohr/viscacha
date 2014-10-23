<h1>Dictionary Convertor: Viscacha Textfiles -> Viscacha MySQL</h1>
<?php
	$original = $_GET['dict'].'.dic';
	$new = $_GET['dict'].'.sql';
	$lines = file($original);
	$lines = array_map('trim', $lines);
	$words = array();
	foreach ($lines as $word) {
		if (strlen($word) > 64) {
			continue;
		}
		$words[] = "{$word},{$_GET['dict']}";
	}
	$c = count($words);
	file_put_contents($new, implode("\n", $words));
	
	$dbh = mysql_connect('localhost', 'root', '19881988');
	$selected = mysql_select_db("mamonede", $dbh);
	$path = realpath('./'.$new);
	$path = addslashes($path);

	$sql = <<<EOD
LOAD DATA LOCAL INFILE '{$path}' INTO TABLE `v_spellcheck` 
FIELDS TERMINATED BY ','
LINES TERMINATED BY '\\n' 
EOD;

	$result = mysql_query($sql) or die(mysql_error().'<br /><br />'.$sql);
	$r = mysql_affected_rows($dbh);
	mysql_close($dbh);

	echo "$c Zeilen durchgegangen, $r eingefügt. Fertig!";
?>