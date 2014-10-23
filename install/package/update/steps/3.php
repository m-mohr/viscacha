<div class="bbody">
<?php
echo "<strong>Starting Update:</strong><br />";

require('../data/config.inc.php');
require_once('lib/function.variables.php');
require_once('../classes/class.phpconfig.php');

function return_array($group, $id) {
	$file = "../language/{$id}/{$group}.lng.php";
	if (file_exists($file)) {
		include($file);
	}
	if (!isset($lang) || !is_array($lang)) {
		$lang = array();
	}
	return $lang;
}

function getLangCodes() {
	global $db;
	$l = array();
	$result = $db->query('SELECT id FROM '.$db->pre.'language ORDER BY language',__LINE__,__FILE__);
	while($row = $db->fetch_assoc($result)) {
		$settings = return_array('settings', $row['id']);
		if (!isset($l[$settings['spellcheck_dict']])) {
			$l[$settings['spellcheck_dict']] = array();
		}
		$l[$settings['spellcheck_dict']] = $row['id'];
	}
	return $l;
}

echo "- Source files loaded<br />";

if (!class_exists('filesystem')) {
	require_once('../classes/class.filesystem.php');
	$filesystem = new filesystem($config['ftp_server'], $config['ftp_user'], $config['ftp_pw'], $config['ftp_port']);
	$filesystem->set_wd($config['ftp_path']);
}
if (!class_exists('DB')) {
	require_once('../classes/database/'.$config['dbsystem'].'.inc.php');
	$db = new DB($config['host'], $config['dbuser'], $config['dbpw'], $config['database'], $config['pconnect'], true, $config['dbprefix']);
	$db->pre = $db->prefix();
	$db->errlogfile = '../'.$db->errlogfile;
}

echo "- FTP class loaded, Database connection started.<br />";

// Hooks
$filesystem->chmod('../admin/data/hooks.txt', 0666);
$hooks = file_get_contents('../admin/data/hooks.txt');
$hooks = str_replace("-register_form_end", "-register_form_end\n-register_resend_start\n-register_resend_form_start\n-register_resend_form_end\n-register_resend2_start\n-register_resend2_check\n-register_resend2_end", $hooks);
$filesystem->file_put_contents('../admin/data/hooks.txt', $hooks);
$hooks = file_get_contents('../admin/data/hooks.txt');
if (strpos($hooks, 'register_resend_form_start') !== false) {
	echo "- Hooks added.<br />";
}
else {
	echo "- Hooks could not be added. Ask the support for help.<br />";
}

// Config
$c = new manageconfig();
$c->getdata('../data/config.inc.php');
$c->updateconfig('version', str, VISCACHA_VERSION);
$c->updateconfig('sessionmails', int, $config['sessionmails'] == 1 ? 0 : 1);
$c->updateconfig('email_check_mx', int, 0);
$c->updateconfig('fullname_posts', int, 1);
$c->updateconfig('lasttopic_chars', int, 40);
$c->updateconfig('pm_user_status', int, 1);
$c->updateconfig('post_user_status', int, 1);
$c->savedata();
echo "- Configuration updated.<br />";

// MySQL [5 compatibility]
$db->query("ALTER TABLE `{$db->pre}bbcode` CHANGE `bbcodeexample` `bbcodeexample` varchar(255) NOT NULL default ''", __LINE__, __FILE__);
$db->query("ALTER TABLE `{$db->pre}bbcode` CHANGE `title` `title` varchar(200) NOT NULL default ''", __LINE__, __FILE__);
$db->query("ALTER TABLE `{$db->pre}bbcode` CHANGE `buttonimage` `buttonimage` varchar(255) NOT NULL default ''", __LINE__, __FILE__);
$db->query("ALTER TABLE `{$db->pre}forums` CHANGE `auto_status` `auto_status` enum('','a','n') NOT NULL default ''", __LINE__, __FILE__);
$db->query("ALTER TABLE `{$db->pre}forums` CHANGE `message_title` `message_title` varchar(255) NOT NULL default ''", __LINE__, __FILE__);
$db->query("ALTER TABLE `{$db->pre}moderators` CHANGE `time` `time` int(10) unsigned default NULL default '0'", __LINE__, __FILE__);
$db->query("ALTER TABLE `{$db->pre}packages` CHANGE `title` `title` varchar(200) NOT NULL default ''", __LINE__, __FILE__);
$db->query("ALTER TABLE `{$db->pre}plugins` CHANGE `name` `name` varchar(200) NOT NULL default ''", __LINE__, __FILE__);
$db->query("ALTER TABLE `{$db->pre}plugins` CHANGE `module` `module` mediumint(7) unsigned NOT NULL default '0'", __LINE__, __FILE__);
$db->query("ALTER TABLE `{$db->pre}postratings` CHANGE `mid` `mid` mediumint(7) NOT NULL default '0'", __LINE__, __FILE__);
$db->query("ALTER TABLE `{$db->pre}postratings` CHANGE `aid` `aid` mediumint(7) NOT NULL default '0'", __LINE__, __FILE__);
$db->query("ALTER TABLE `{$db->pre}postratings` CHANGE `tid` `tid` int(10) NOT NULL default '0'", __LINE__, __FILE__);
$db->query("ALTER TABLE `{$db->pre}postratings` CHANGE `pid` `pid` int(10) NOT NULL default '0'", __LINE__, __FILE__);
$db->query("ALTER TABLE `{$db->pre}replies` CHANGE `ip` `ip` varchar(20) NOT NULL default ''", __LINE__, __FILE__);
$db->query("ALTER TABLE `{$db->pre}settings` CHANGE `sgroup` `sgroup` smallint(4) unsigned NOT NULL default '0'", __LINE__, __FILE__);
$db->query("ALTER TABLE `{$db->pre}settings_groups` CHANGE `title` `title` varchar(120) NOT NULL default ''", __LINE__, __FILE__);
$db->query("ALTER TABLE `{$db->pre}settings_groups` CHANGE `name` `name` varchar(120) NOT NULL default ''", __LINE__, __FILE__);
$db->query("ALTER TABLE `{$db->pre}user` CHANGE `timezone` `timezone` varchar(5) default NULL default ''", __LINE__, __FILE__);
echo "- Database tables updated (MySQL 5 compatibility).<br />";

// templates
$dir = "../templates/";
$tplids = array();
$d = dir($dir);
while (false !== ($entry = $d->read())) {
	if (is_dir($dir.$entry) && preg_match('/^\d{1,}$/', $entry) && $entry != '.' && $entry != '..') {
		$tplids[] = $entry;
	}
}
$d->close();
foreach ($tplids as $id) {
	$tpldir = $dir.$id;
	$filesystem->unlink($tpldir.'/register.html');
	$filesystem->chmod($tpldir.'/header.html', 0666);
	$header = file_get_contents($tpldir.'/header.html');
	$header = str_replace("<!--[if IE]>", "<!--[if lt IE 7]>", $header);
	$filesystem->file_put_contents($tpldir.'/header.html', $header);
}
echo "- Templates updated.<br />";

// language files
$ini = array(
	'custom' => array(
		'language' => array(
			'credits' => 'Credits',
			'imprint' => 'Imprint'
		),
		'language_de' => array(
			'credits' => 'Credits',
			'imprint' => 'Impressum'
		)
	),
	'global' => array(
		'language' => array(
			'ats_choose' => 'No Status / Standard',
			'ats_choose_desc' => 'This forum allows you to mark topics as good or bad.  You should consider the following when rating threads:<br /> <em>Good:</em>Threads rated "good" will be given priority by the system. Threads should be rated as good if they successfully resolve problems that are frequently encountered on the board. Any content that you feel is beneficial should be marked "good"<br /> <em>Bad:</em>These topics will be given less priority by the system and can be automatically ignored by board members. Threads that are off topic, sensless or otherwise useless should get this mark.<br /> <em>article:</em>Threads that are marked as "article" will be listed in the article overview and can be easily access this way.<br /> <em>News:</em> News Threads are posted to the internal news system and will be offered to the readers via the board itself and alternativly on other websites via rss-feed.',
			'ats_choose_standard_a' => ' (Standard: Article)',
			'ats_choose_standard_n' => ' (Standard: News)',
			'bbcodes_tt' => 'Typewriter text',
			'log_wrong_data' => 'You entered invalid login data or your account has not yet been activated.<br />Did you <a href="log.php?action=pwremind">forget your password</a>? If you have not received a validation e-mail click <a href="register.php?action=resend">here</a>.',
			'post_quote_direct' => 'Quote post directly',
			'post_quote_multi' => 'Save post for quoting (Multiquote)',
			'register_resend_desc' => 'Please enter your registered member name below to search for any pending validation requests. If any are found, the email will be resent to the email address you registered with.',
			'register_resend_no_user' => 'Sorry, but not member with this name or a pending registration was found. Maybe the administrator has to activate your account.',
			'register_resend_success' => 'An e-mail, required for account activation, has been sent to you again.',
			'register_resend_title' => 'Resend validation email',
			'textarea_check_length' => 'Check length',
			'textarea_decrease_size' => 'Decrease Size',
			'textarea_increase_size' => 'Increase Size',
			'th_status' => 'Status'
		),
		'language_de' => array(
			'ats_choose' => 'Kein Status / Standard',
			'ats_choose_desc' => 'In dieser Forensoftware ist es möglich Themen als Gut, Schlecht, Artikel oder Nachricht zu markieren. Das bedeuten die einzelnen Typen:<br /> <em>Gut:</em> Themen können als "Gut" markiert werden. Sie werden dann als solche markiert und bevorzugt. Man sollte Themen als Gut markieren wenn Sie häufig nachgefragt werden und in diesem Thema die Lösung zu diesem Problem steht. Weiterhin sollten gute Diskussionen als solche markiert werden.<br /> <em>Schlecht:</em> Das Gegenteil zu "Gut". Themen werden nicht bevorzugt und können von den Mitgliedern automatisch ausgeblendet werden. Themen die zu sehr vom Thema abschweifen oder nicht wirklich Sinn haben, sollten mit diesem Status belegt werden.<br /> <em>Artikel:</em> Themen die als Artikel bezeichnet werden, werde in der Artikelübersicht gelistet und können so komfortabel duchgesehen werden.<br /> <em>Nachricht:</em> Mit "Nachricht" markierte Themen werden in einem eigenen Newssystem zum durchlesen angeboten und können auf der eigenen Homepage eingebunden werden.',
			'ats_choose_standard_a' => ' (Standard: Artikel)',
			'ats_choose_standard_n' => ' (Standard: News)',
			'bbcodes_tt' => 'Schreibmaschinenschrift',
			'log_wrong_data' => 'Sie haben falsche Benutzerdaten angegeben oder Sie sind noch nicht freigeschaltet.<br />Benutzen Sie die <a href="log.php?action=pwremind">Passwort vergessen</a>-Funktion wenn Sie Ihr Passwort nicht mehr wissen. Falls Sie keine Freischalt-E-Mail bekommen haben, klicken Sie <a href="register.php?action=resend">hier</a>.',
			'post_quote_direct' => 'Beitrag direkt zitieren',
			'post_quote_multi' => 'Beitrag zum Zitieren merken (Multiquote)',
			'register_resend_desc' => 'Solltest Du Dich bereits registriert - aber den Bestätigungslink der Registrierungs-E-Mail noch nicht angeklickt haben, konnte Deine Registrierung nicht vollständig abgeschlossen werden. Du hast hier die Möglichkeit, Dir diese E-Mail erneut zuschicken zu lassen, ohne den Registrierungsvorgang wiederholen zu müssen. Dazu trägst Du lediglich den bereits von Dir beantragten Benutzernamen ein - und die E-Mail wird Dir erneut an Deine bereits bei der Registrierung angegebene E-Mail-Adresse übersandt.',
			'register_resend_no_user' => 'Es wurde leider kein Benutzer mit diesem Namen oder einer benötigten Freischaltung gefunden. Eventuell muss der Administrator Sie noch freischalten.',
			'register_resend_success' => 'Die Aktivierungs-E-Mail wurde Ihnen erneut zugeschickt.',
			'register_resend_title' => 'Registrierungs-E-Mail erneut verschicken',
			'textarea_check_length' => 'Überprüfe Textlänge',
			'textarea_decrease_size' => 'Verkleinern',
			'textarea_increase_size' => 'Vergrößern',
			'th_status' => 'Status'
		)
	),
	'javascript' => array(
		'language' => array(
			'js_quote_multi' => 'Save post for quoting (Multiquote)',
			'js_quote_multi_2' => 'Removed saved post (Multiquote)',
			'js_ta_left' => ' characters left.',
			'js_ta_max' => 'The maximum allowed length is: ',
			'js_ta_too_much' => ' characters too much.',
			'js_ta_used' => 'You have '
		),
		'language_de' => array(
			'js_quote_multi' => 'Beitrag zum Zitieren merken (Multiquote)',
			'js_quote_multi_2' => 'Gemerktes Zitat entfernen (Multiquote)',
			'js_ta_left' => ' Zeichen übrig.',
			'js_ta_max' => 'Die maximal erlaubte Länge ist: ',
			'js_ta_too_much' => ' Zeichen zu viel benutzt.',
			'js_ta_used' => 'Sie haben '
		)
	),
	'wwo' => array(
		'language' => array(
			'wwo_log_logout' => 'is logging out'
		),
		'language_de' => array(
			'wwo_log_logout' => 'Meldet sich ab'
		)
	)
);

$c = new manageconfig();
$codes = array();
$keys = array('language', 'language_de');
foreach ($keys as $entry) {
   	if (preg_match('~language_(\w{2})_?(\w{0,2})~i', $entry, $code)) {
   		if (!isset($codes[$code[1]])) {
   			$codes[$code[1]] = array();
   		}
   		if (isset($code[2])) {
   			$codes[$code[1]][] = $code[2];
   		}
   		else {
   			if (!in_array('', $codes[$code[1]])) {
   				$codes[$code[1]][] = '';
   			}
   		}
   	}
}
$langcodes = getLangCodes();
foreach ($langcodes as $code => $lid) {
	$ldat = explode('_', $code);
	if (isset($codes[$ldat[0]])) {
		$count = count($codes[$ldat[0]]);
		if (in_array('', $codes[$ldat[0]])) {
			$count--;
		}
	}
	else {
		$count = -1;
	}
	if (isset($codes[$ldat[0]]) && !empty($ldat[1]) && in_array($ldat[1], $codes[$ldat[0]])) { // Nehme Original
		$src = 'language_'.$code;
	}
	elseif(isset($codes[$ldat[0]]) && in_array('', $codes[$ldat[0]])) { // Nehme gleichen Langcode, aber ohne Countrycode
		$src = 'language_'.$ldat[0];
	}
	elseif(isset($codes[$ldat[0]]) && $count > 0) { // Nehme gleichen Langcode, aber falchen Countrycode
		$src = 'language_'.$ldat[0].'_'.reset($codes[$ldat[0]]);
	}
	else { // Nehme Standard
		$src = 'language';
	}
	foreach($ini as $file => $data){
		$c->getdata("../language/{$lid}/{$file}.lng.php", 'lang');
		foreach ($data[$src] as $varname => $text) {
			$c->updateconfig($varname, str, $text);
		}
		$c->savedata();
	}
}

echo "- Language files updated.<br />";

// Delete old files
$filesystem->unlink('../classes/cache/team_ag.inc.php');
$filesystem->unlink('../classes/cache/groupstandard.inc.php');
$filesystem->unlink('../classes/cache/group_status.inc.php');
echo "- Old files deleted.<br />";

// Refresh Cache
$dirs = array('../cache/', '../cache/modules/');
foreach ($dirs as $dir) {
	if ($dh = @opendir($dir)) {
		while (($file = readdir($dh)) !== false) {
			if (strpos($file, '.php') !== false) {
				$filesystem->unlink($dir.$file);
			}
	    }
		closedir($dh);
	}
}
echo "- Cache cleared.<br />";
echo "<br /><strong>Finished Update!</strong>";
?>
</div>
<div class="bfoot center"><input type="submit" value="Continue" /></div>