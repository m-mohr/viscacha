<?php
include('../data/config.inc.php');
require_once('../classes/class.filesystem.php');
$filesystem = new filesystem($config['ftp_server'], $config['ftp_user'], $config['ftp_pw'], $config['ftp_port']);
$filesystem->set_wd($config['ftp_path']);
if (isset($_REQUEST['save']) && $_REQUEST['save'] == 1) {
	if (isset($_REQUEST['action']) && is_array($_REQUEST['action'])) {
		$action = $_REQUEST['action'];
	}
	else {
		$action = array();
	}
	require_once('../classes/database/'.$config['dbsystem'].'.inc.php');
	$db = new DB($config['host'], $config['dbuser'], $config['dbpw'], $config['database'], $config['pconnect'], false, $config['dbprefix']);
	$db->errlogfile = '../'.$db->errlogfile;
	$db->pre = $db->prefix();
	$db->connect(false);
	if (!is_resource($db->conn)) {
		?>
	<div class="bbody">Could not connect to database! Pleasy try again later or check the database settings!</div>
	<div class="bfoot center"><a class="submit" href="index.php?step=<?php echo $step-2; ?>">Go back</a> <a class="submit" href="index.php?step=<?php echo $step; ?>">Refresh</a></div>
		<?php
	}
	else {
		if (!$db->select_db()) {
			?>
	<div class="bbody">Could not find database <em><?php echo $db->database; ?></em>! Pleasy create a new database with this name or choose another database!</div>
	<div class="bfoot center"><a class="submit" href="index.php?step=<?php echo $step-2; ?>">Go back</a> <a class="submit" href="index.php?step=<?php echo $step; ?>">Refresh</a></div>
			<?php
		}
		else {
			// ToDo: Übersetzen
			$act = array(
			3 => 'Tabelle(n) wurden gelöscht und neu erstellt',
			2 => 'Tabelle(n) wurden nicht geändert',
			1 => 'Tabelle(n) wurden geleert',
			0 => 'Tabelle(n) wurden neu erstellt',
			);
			$done = array_fill(0, 4, 0);
			foreach ($action as $table => $value) {
				$t = $db->pre.$table;
				$file = 'package/'.$package.'/db/'.$table.'.sql';
				if ($value == 0) {
					$sql = implode('', file($file));
					$sql = str_replace('{:=DBPREFIX=:}', $db->pre, $sql);
					$db->multi_query($sql);
				}
				elseif ($value == 1) {
					$db->query("TRUNCATE TABLE `{$t}`");
				}
				elseif ($value == 3) {
					$sql = implode('', file($file));
					$sql = str_replace('{:=DBPREFIX=:}', $db->pre, $sql);
					$drop = "DROP TABLE IF EXISTS `{$t}`;\n";
					$db->multi_query($drop.$sql);
				}
				else {
					$value = 2;
					// Do nothing with tables!
				}
				$done[$value]++;
			}
			echo '<div class="bfoot">';
			if (array_sum($done) == 0) {
				echo "<strong>No changes applied!</strong>";
			}
			else {
				foreach ($act as $id => $name) {
					if ($done[$id] > 0) {
						echo "<strong>{$done[$id]} {$name}.</strong><br />";
					}
				}
			}
			echo '</div>';
		}
	}
	$db->close();
}
?>
<div class="bbody">
	<input type="hidden" name="save" value="1" />
	<label for="name">Benutzername:</label>
	<input class="label" id="name" name="name" size="40" />
	<br class="newinput" /><hr class="formsep" />
	<label for="pw">Passwort:</label>
	<input class="label" type="password" id="pw" name="pw" size="40" />
	<br class="newinput" /><hr class="formsep" />
	<label for="pwx">Passwort wiederholen:</label>
	<input class="label" type="password" id="pwx" name="pwx" size="40" />
	<br class="newinput" /><hr class="formsep" />
	<label for="email">E-Mail-Adresse:</label>
	<input class="label" type="text" id="email" name="email" size="40" value="<?php echo $config['forenmail']; ?>" /> 
	<br class="newinput" /><br class="iefix_br" />
</div>
<div class="bfoot center"><input type="submit" value="Continue" /></div>
