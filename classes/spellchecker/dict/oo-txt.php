<h1>Dictionary Convertor: OpenOffice -> Viscacha</h1>
<?php
    $word_seperator = "0-9\\.,;:!\\?\\-\\|\n\r\s\"'\\[\\]\\{\\}\\(\\)\\/\\\\";
	$original = $_GET['dict'].'.dic';
	$new = $_GET['dict'].'.dic';
	$lines = file($original);
	$words = array();
	$lines = array_map('trim', $lines);
	$i = 0;
	foreach ($lines as $word) {
		if ($i == 0) {
			$i++;
			continue;
		}
		$char = strpos($word, '/');
		if ($char !== FALSE) {
			$word = substr($word, 0, $char);
		}
		$xwords = preg_split('/['.$word_seperator.']+?/', $word, -1, PREG_SPLIT_NO_EMPTY);
		foreach ($xwords as $w) {
			if (strlen($w) > 1) {
				$i++;
				$words[] = $w;
			}
		}
	}
	$words = array_unique($words);
	$c = count($words);
	file_put_contents($new, stripslashes(implode("\n", $words)));
	echo "$i Zeilen durchgegangen; $c Zeilen erhalten. Fertig!";
?>