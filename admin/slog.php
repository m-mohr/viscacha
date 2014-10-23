<?php
if (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == "slog.php") die('Error: Hacking Attempt');

function getmonth($number) {
	global $months;
	return $months[$number - 1];
}
function getday($number) {
	global $days, $lang;
	return $days[$number];
}
function daynumber($time) {
	$daynumber = intval(date('w', $time)) - 1;
	if ($daynumber < 0) $daynumber = 7 + $daynumber;
	return $daynumber;
}

if ($job == 'empty') {
	echo head();
	$file = $gpc->get('file', str);
	if ($file == 'l_mysqlerror') {
	    $filename = 'data/errlog_mysql.inc.php';
	}
	elseif ($file == 'l_cron') {
	    $filename = 'data/cron/cron.log';
	}
	if (isset($filename) && file_exists($filename)) {
	    $filesystem->file_put_contents($filename,'');
	    ok('admin.php?action=slog&job='.$file, 'Logfile was successfully deleted');
	}
	else {
        error('admin.php?action=slog&job='.$file, 'Logfile not found');
	}
}
elseif ($job == 'l_mysqlerror') {
    echo head();
	$log = @file('data/errlog_mysql.inc.php');
	if (!is_array($log) || count($log) < 1) {
		$log = 'Logfile is empty!';
	}
	?>
<form name="form" method="post" action="admin.php?action=slog&file=l_mysqlerror&job=empty">
 <table class="border">
  <tr> 
   <td class="obox" colspan="8">MySQL-Error-Logfile</td>
  </tr>
   <?php
	if (!is_array($log)) {
		echo '<tr class="mbox"><td colspan="8">'.$log.'</td></tr>';
	}
	else {
   ?>
   <tr class="ubox">
    <td>Errormessage</td>
    <td>Query</td>
    <td>Information</td>
    <td>Date</td>
   </tr>
   <?php
   foreach ($log as $row) {
   $data =  explode("\t", $row);
   ?>
   <tr class="mbox">
    <td><textarea cols="35" rows="3" class="stext"><?php echo $data[0]; ?>: <?php echo $data[1]; ?></textarea></td>
    <td><textarea cols="37" rows="3" class="stext"><?php echo $data[4]; ?></textarea></td>
    <td>
<textarea cols="39" rows="3" class="stext">File: <?php echo $data[2]; ?>

Line: <?php echo $data[3]; ?>

URL: <?php echo $data[5]; ?></textarea>
    </td>
    <td class="stext"><?php echo date("D, j M Y", $data[6]); ?><br /><?php echo date("G:i:s O", $data[6]); ?></td>
   </tr>
   <?php } ?>
      </table>
   <?php } ?>
   </td>
  </tr>
  <tr> 
   <td class="ubox" align="center" colspan="8"><input type="submit" name="Submit" value="Log-Datei jetzt leeren!"></td> 
  </tr>
 </table>
</form> 
	<?php
	echo foot();
}
elseif ($job == 'l_cron') {
	echo head();
	$log = @file('data/cron/cron.log');
	if (!is_array($log) || count($log) < 1) {
		$log = 'Logfile is empty!';
	}
	else {
		$log = "<pre>".implode("", $log)."</pre>";
	}
	?>
<form name="form" method="post" action="admin.php?action=slog&file=l_cron&job=empty">
 <table class="border">
  <tr> 
   <td class="obox">Scheduled Tasks: Logfile</td>
  </tr>
  <tr> 
   <td class="mbox"><?php echo $log; ?></td>
  </tr>
  <tr> 
   <td class="ubox" align="center"><input type="submit" name="Submit" value="Log-Datei jetzt leeren!"></td> 
  </tr>
 </table>
</form> 
	<?php
	echo foot();
}
elseif ($job == 's_general_image') {
	
	require_once('classes/class.charts.php');
	$PG = new PowerGraphic();

	$skin = $gpc->get('skin', int, 1);
	$modus = $gpc->get('modus', int, 1);

	$type = $gpc->get('dtype', int);
	$sql = '';
	switch ($type) {
		case 1: 
			$table = $db->pre."user";
			$datefield = "regdate";
			$stats_name = 'Registrierungen';
		break;
		case 2: 
			$table = $db->pre."topics";
			$datefield = "date";
			$stats_name = 'Themen';
		break;
		case 3: 
			$table = $db->pre."replies";
			$datefield = "date";
			$stats_name = 'Antworten';
		break;
		default: 
			$table = $db->pre."pm";
			$datefield = "date";
			$sql = ' AND dir != "2" ';
			$stats_name = 'Private Nachrichten';
		break;
	}
	
	$timeorder = $gpc->get('timeorder', int);
	switch ($timeorder) {
		case 1:
			$sqlformat = "%U %m %Y";
			$phpformat = "d.m.Y";
		break;
		case 2:
			$sqlformat = "%U %Y";
			$phpformat = "# (n~ Y)";
		break;
		default:
			$sqlformat = "%m %Y";
			$phpformat = "n~ Y";
	}
	
	$sort = $gpc->get('sortorder', str);
	if ($sort == 'asc' || $sort == 'desc') {
		$sort = strtoupper($sort);
	}
	else {
		$sort = 'ASC';
	}
	
	$to = mktime(24, 0, 0, $gpc->get('to_month', int), $gpc->get('to_day', int), $gpc->get('to_year', int));
	$from = mktime(0, 0, 0, $gpc->get('from_month', int), $gpc->get('from_day', int), $gpc->get('from_year', int));
	
	$max = 0;
	$cache = array();
	$result = $db->query("SELECT COUNT(*), DATE_FORMAT(FROM_UNIXTIME($datefield),'$sqlformat') AS timeorder, MAX($datefield) AS statdate FROM $table WHERE $datefield > '$from' AND $datefield < '$to' $sql GROUP BY timeorder ORDER BY $datefield $sort", __LINE__, __FILE__);
	while ($row = $db->fetch_array($result)) {
		$statdate = date($phpformat, $row['statdate']);
		
		if ($timeorder == 1) $statdate = preg_replace("/(\d+)~/e", "getday('\\1')", $statdate);
		if ($timeorder > 1) $statdate = preg_replace("/(\d+)~/e", "getmonth('\\1')", $statdate);
		if ($timeorder == 2) {
			$week = ceil((date('z', $row['statdate']) - daynumber($row['statdate'])) / 7) + ((daynumber(mktime(0, 0, 0, 1, 1, date('Y', $row['statdate']))) <= 3) ? (1) : (0));
			if ($week == 53 && daynumber(mktime(0, 0, 0, 12, 31, date('Y', $row['statdate']))) < 3) {
				$tempRow = $db->fetch_array($result);
				$row[0] += $tempRow[0];
				$week = 1;
			}
			$statdate = str_replace("#", "#".$week, $statdate);
		}
			
		if ($row[0] > $max) $max = $row[0];
		$cache[] = array($row[0], $statdate);
	}
	
	$PG->title     = $stats_name;
	$PG->axis_x    = '';
	$PG->axis_y    = $stats_name;
	$PG->type      = $modus;
	$PG->skin      = $skin;
	$PG->dp 	   = '.';
	$PG->ds 	   = '';
	
	if (count($cache)) {
		while (list($key, $row) = each($cache)) {
			$PG->x[] = $row[1];
			$PG->y[] = $row[0];
		}
	}

	$PG->credits   = 'Viscacha '.$config['version'];
	
	$PG->start();
}
elseif ($job == 's_general') {
	echo head();
	
	$result = $db->query('SELECT MIN(regdate) as date FROM '.$db->pre.'user LIMIT 1');
	$install = $db->fetch_assoc($result);
	
	$show = $gpc->get('show', int);
	require_once("classes/class.charts.php");
	$PG = new PowerGraphic();
	$skin = $gpc->get('skin', int, 1);
	$modus = $gpc->get('modus', int, 1);
	$type = $gpc->get('dtype', int, 3);
	$timeorder = $gpc->get('timeorder', int, 2);
	$sortorder = $gpc->get('sortorder', str, 'asc');
	?>
<form method="post" action="admin.php?action=slog&job=s_general&show=1">
 <table border="0" class="border">
  <tr class="obox">
   <td colspan="2">Statistik-Filter</td>
  </tr>
  <tr class="mbox">
   <td>Art der Statistik</td>
   <td><select name="dtype">
    <option value="1"<?php echo iif($type == 1,' selected="selected"'); ?>>Registrierungen</option>
    <option value="2"<?php echo iif($type == 2,' selected="selected"'); ?>>Themen</option>
    <option value="3"<?php echo iif($type == 3,' selected="selected"'); ?>>Beitr&auml;ge</option>
    <option value="4"<?php echo iif($type == 4,' selected="selected"'); ?>>Private Nachrichten</option>
   </select></td>
  </tr>
  <tr class="mbox">
   <td>Statistik von</td>
   <td>
   <select name="from_day">
	<?php
	$from_day = $gpc->get('from_day', int, date('j', $install['date']));
	for ($i=1;$i<=31;$i++) {
		if ($from_day == $i) $bdsel = ' selected="selected"';
		else $bdsel = '';
		echo "<option value='".$i."'".$bdsel.">".$i."</option>\n";
	}
	?>
   </select>
   <select name="from_month">
	<?php
	$from_month = $gpc->get('from_month', int, date('n', $install['date']));
	for ($i=1;$i<=12;$i++) {
		if ($from_month == $i) $bmsel = ' selected="selected"';
		else $bmsel = '';
		echo "<option value='".$i."'".$bmsel.">".getmonth($i)."</option>\n";
	}
	?>
   </select>
   <select name="from_year">
	<?php
	$from_year = $gpc->get('from_year', int, date('Y', $install['date']));
	for ($i=date('Y');$i>=2000;$i--) {
		if ($from_year == $i) $bysel = ' selected="selected"';
		else $bysel = '';
		echo "<option value='".$i."'".$bysel.">".$i."</option>\n";
	}
	?>
   </select>
   </td>
  </tr>
  <tr class="mbox">
   <td>Statistik bis</td>
   <td>
   <select name="to_day">
	<?php
	$to_day = $gpc->get('to_day', int, date('j'));
	for ($i=1;$i<=31;$i++) {
		if ($to_day == $i) $bdsel = ' selected="selected"';
		else $bdsel = '';
		echo "<option value='".$i."'".$bdsel.">".$i."</option>\n";
	}
	?>
   </select>
   <select name="to_month">
	<?php
	$to_month = $gpc->get('to_month', int, date('n'));
	for ($i=1;$i<=12;$i++) {
		if ($to_month == $i) $bmsel = ' selected="selected"';
		else $bmsel = '';
		echo "<option value='".$i."'".$bmsel.">".getmonth($i)."</option>\n";
	}
	?>
   </select>
   <select name="to_year">
	<?php
	$to_year = $gpc->get('to_year', int, date('Y'));
	for ($i=$to_year;$i>=2000;$i--) {
		if (date('Y') == $i) $bysel = ' selected="selected"';
		else $bysel = '';
		echo "<option value='".$i."'".$bysel.">".$i."</option>\n";
	}
	?>
   </select>
   </td>
  </tr>
  <tr class="mbox">
   <td>Zeitliche Ordnung</td>
   <td><select name="timeorder">
    <option value="1"<?php echo iif($timeorder == 1,' selected="selected"'); ?>>T&auml;glich</option>
    <option value="2"<?php echo iif($timeorder == 2,' selected="selected"'); ?>>W&ouml;chentlich</option>
    <option value="3"<?php echo iif($timeorder == 3,' selected="selected"'); ?>>Monatlich</option>
   </select></td>
  </tr>
  <tr class="mbox">
   <td>Sortierung</td>
   <td><select name="sortorder">
    <option value="asc"<?php echo iif($sortorder == 'asc',' selected="selected"'); ?>>Aufsteigend</option>
    <option value="desc"<?php echo iif($sortorder == 'desc',' selected="selected"'); ?>>Absteigend</option>
   </select></td>
  </tr>
  <tr class="mbox">
   <td>Type</td>
   <td><select name="modus">
<?php foreach ($PG->available_types as $code => $type) { ?>
    <option value="<?php echo $code; ?>"<?php echo iif($code == $modus, ' selected="selected"'); ?>><?php echo $type; ?></option>
<?php } ?>
</select></td>
  </tr>
  <tr class="mbox">
   <td>Design</td>
   <td><select name="skin">
<?php foreach ($PG->available_skins as $code => $color) { ?>
    <option value="<?php echo $code; ?>"<?php echo iif($code == $skin, ' selected="selected"'); ?>><?php echo $color; ?></option>
<?php } ?>
</select></td>
  </tr>
  <tr class="ubox">
   <td colspan="2" align="center"><input type="submit" value="Create" /></td>
  </tr>
 </table>
</form>
<br class="minibr" />
<?php
if ($show == 1) {
	$query = $_POST;
	$query['job'] = 's_general_image';
	$query['action'] = 'slog';
	$url = "admin.php?".http_build_query($query);
?>
<table border="0" class="border">
  <tr class="obox">
   <td>Statistik-Ausgabe</td>
  </tr>
  <tr class="mbox">
   <td><a href="<?php echo $url; ?>"><img src="<?php echo $url; ?>" style="border: 1px solid #000000;" alt="Statistics"></a></td>
  </tr>
</table>
<?php
	}
	else {
	$result = $db->query('SELECT COUNT(*) FROM '.$db->pre.'replies',__LINE__,__FILE__);
	$posts = $db->fetch_array($result);
	$result = $db->query('SELECT COUNT(*) FROM '.$db->pre.'topics',__LINE__,__FILE__);
	$topics = $db->fetch_array($result);
	$replies = $posts[0]-$topics[0];

	$result = $db->query('SELECT COUNT(*) FROM '.$db->pre.'topics WHERE mark = "a"',__LINE__,__FILE__);
	$aposts = $db->fetch_array($result);
	$result = $db->query('SELECT COUNT(*) FROM '.$db->pre.'topics WHERE mark = "n"',__LINE__,__FILE__);
	$nposts = $db->fetch_array($result);

	$result = $db->query('SELECT COUNT(*) FROM '.$db->pre.'topics WHERE vquestion != ""',__LINE__,__FILE__);
	$vote = $db->fetch_array($result);

	$result = $db->query('SELECT COUNT(*) FROM '.$db->pre.'user',__LINE__,__FILE__);
	$members = $db->fetch_array($result);
	
	$result = $db->query('SELECT COUNT(*) FROM '.$db->pre.'abos',__LINE__,__FILE__);
	$abos = $db->fetch_array($result);

	$result = $db->query('SELECT COUNT(*) FROM '.$db->pre.'fav',__LINE__,__FILE__);
	$favs = $db->fetch_array($result);

	$result = $db->query('SELECT COUNT(*) FROM '.$db->pre.'uploads',__LINE__,__FILE__);
	$uploads = $db->fetch_array($result);
	
	$result = $db->query('SELECT COUNT(*) FROM '.$db->pre.'votes',__LINE__,__FILE__);
	$votes = $db->fetch_array($result);
?>
 <table class="border">
  <tr> 
   <td class="obox">General Statistics</td>
  </tr>
  <tr> 
   <td class="mbox">
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
	<tr><td>Members:</td><td><code><?php echo $members[0];?></code></td></tr>
	<tr><td>Posts:</td><td><code><?php echo $posts[0];?></code></td></tr>
	<tr>
	  <td>Threads:</td><td><code><?php echo $topics[0];?></code></td>
	  <td>Replies:</td><td><code><?php echo $replies;?></code></td>
	</tr>
	<tr>
	  <td>Articles:</td><td><code><?php echo $aposts[0];?></code></td>
	  <td>News:</td><td><code><?php echo $nposts[0];?></code></td>
	</tr>
	<tr><td>Attachments:</td><td><code><?php echo $uploads[0];?></code></td></tr>
	<tr>
	  <td>Subscriptions:</td><td><code><?php echo $abos[0];?></code></td>
	  <td>Favourite threads:</td><td><code><?php echo $favs[0];?></code></td>
	</tr>
	<tr>
	  <td width="25%">Polls:</td><td width="25%"><code><?php echo $vote[0];?></code></td>
	  <td width="25%">Voter:</td><td width="25%"><code><?php echo $votes[0];?></code></td>
	</tr>
	</table>
   </td> 
  </tr>
 </table>
	<?php
	}
	echo foot();
}
?>
