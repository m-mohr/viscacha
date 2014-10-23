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

// Config
$c = new manageconfig();
$c->getdata('../data/config.inc.php');
$c->updateconfig('version', str, VISCACHA_VERSION);
$c->updateconfig('allow_http_auth', int, 0);
$c->delete(array('module_1', 'relatednum'));
$c->delete(array('module_3', 'items'));
$c->delete(array('module_3', 'teaserlength'));
$c->delete(array('module_4', 'feed'));
$c->delete(array('module_4', 'title'));
$c->delete(array('module_7', 'text'));
$c->delete(array('module_7', 'title'));
$c->delete(array('module_9', 'topicnum'));
$c->delete(array('module_10', 'repliesnum'));
$c->updateconfig(array('viscacha_addreply_last_replies', 'repliesnum'), int, 5);
$c->updateconfig(array('viscacha_news_boxes', 'cutat'), str, 'teaser');
$c->updateconfig(array('viscacha_news_boxes', 'items'), int, 5);
$c->updateconfig(array('viscacha_news_boxes', 'teaserlength'), int, 300);
$c->updateconfig(array('viscacha_recent_topics', 'topicnum'), int, 10);
$c->updateconfig(array('viscacha_related_topics', 'hide_empty'), int, 1);
$c->updateconfig(array('viscacha_related_topics', 'relatednum'), int, 5);
$c->savedata();
$c = new manageconfig();
$c->getdata('../admin/data/config.inc.php', 'admconfig');
$c->updateconfig('nav_positions', str, 'left=Left Navigation'."\r\n".'bottom=Bottom Navigation');
$c->updateconfig('package_server', str, 'http://files.viscacha.org/');
$c->savedata();
echo "- Configuration updated.<br />";

// Old files
$filesystem->unlink('../admin/data/lang_email.php');
$filesystem->unlink('../admin/lib/language.inc.php');
$filesystem->unlink('../spellcheck.php');
$filesystem->unlink('../team.php');
$filesystem->unlink('../data/banned.php');
$filesystem->unlink('../classes/graphic/text2image.php');
$filesystem->unlink('../images/1/word.gif');
$filesystem->unlink('../classes/graphic/text2image.php');

echo "- Old files deleted.<br />";

// MySQL
$file = 'package/'.$package.'/db/db_changes.sql';
$sql = implode('', file($file));
$sql = str_replace('{:=DBPREFIX=:}', $db->pre, $sql);
$db->multi_query($sql);
echo "- Database tables updated.<br />";

$ini = array(
	'custom' => array(
		'language' => array(
			'navigation' => 'Main Menu',
			'n_forum' => 'Forums',
			'n_portal' => 'Portal'
		),
		'language_de' => array(
			'navigation' => 'Hauptmenü',
			'n_forum' => 'Forum',
			'n_portal' => 'Portal'
		)
	),
	'modules' => array(
		'language' => array(
			'mymenu_newpm_1' => null,
			'mymenu_newpm_2' => null,
			'birthdaybox_module' => null,
			'wwo_nav_detail' => 'Members: {@wwo->r}<br />Guests: {@wwo->g}<br />Spiders: {@wwo->b}',
			'mymenu_newpm' => 'You have <strong>{%my->pms}</strong> new PM(s)!',
			'last_private_message' => 'Last private message'
		),
		'language_de' => array(
			'mymenu_newpm_1' => null,
			'mymenu_newpm_2' => null,
			'birthdaybox_module' => null,
			'wwo_nav_detail' => 'Mitglieder: {@wwo->r}<br />Gäste: {@wwo->g}<br />Suchmaschinen: {@wwo->b}',
			'mymenu_newpm' => 'Sie haben <strong>{%my->pms}</strong> neue PN!',
			'last_private_message' => 'Letzte private Nachricht'
		)
	),
	'settings' => array(
		'language' => array(
			'compatible_version' => VISCACHA_VERSION
		),
		'language_de' => array(
			'compatible_version' => VISCACHA_VERSION
		)
	),
	'wwo' => array(
		'language' => array(
			'wwo_misc_report_post' => 'is reporting a post to the administration',
			'wwo_team' => 'is viewing the <a href="members.php?action=team">team overview</a>'
		),
		'language_de' => array(
			'wwo_misc_report_post' => 'melden einen Beitrag',
			'wwo_team' => 'Betrachtet die <a href="members.php?action=team">Teamübersicht</a>'
		)
	),
	'global' => array(
		'language' => array(
			'bbcodes_align' => null,
			'bbcodes_align_center' => null,
			'bbcodes_align_desc' => null,
			'bbcodes_align_justify' => null,
			'bbcodes_align_left' => null,
			'bbcodes_align_right' => null,
			'bbcodes_align_title' => null,
			'bbcodes_bold' => null,
			'bbcodes_bold_desc' => null,
			'bbcodes_code' => null,
			'bbcodes_code_desc' => null,
			'bbcodes_color' => null,
			'bbcodes_color_desc' => null,
			'bbcodes_color_title' => null,
			'bbcodes_edit' => null,
			'bbcodes_edit_desc' => null,
			'bbcodes_email' => null,
			'bbcodes_email_desc' => null,
			'bbcodes_example_text' => null,
			'bbcodes_example_text2' => null,
			'bbcodes_expand' => null,
			'bbcodes_header' => null,
			'bbcodes_header_desc' => null,
			'bbcodes_header_h1' => null,
			'bbcodes_header_h2' => null,
			'bbcodes_header_h3' => null,
			'bbcodes_header_title' => null,
			'bbcodes_help' => null,
			'bbcodes_help_example' => null,
			'bbcodes_help_output' => null,
			'bbcodes_help_syntax' => null,
			'bbcodes_hide' => null,
			'bbcodes_hide_desc' => null,
			'bbcodes_hr' => null,
			'bbcodes_hr_desc' => null,
			'bbcodes_img' => null,
			'bbcodes_img_desc' => null,
			'bbcodes_italic' => null,
			'bbcodes_italic_desc' => null,
			'bbcodes_list' => null,
			'bbcodes_list_desc' => null,
			'bbcodes_list_ol' => null,
			'bbcodes_note' => null,
			'bbcodes_note_desc' => null,
			'bbcodes_note_prompt1' => null,
			'bbcodes_note_prompt2' => null,
			'bbcodes_option' => null,
			'bbcodes_ot' => null,
			'bbcodes_ot_desc' => null,
			'bbcodes_param' => null,
			'bbcodes_quote' => null,
			'bbcodes_quote_desc' => null,
			'bbcodes_reader' => null,
			'bbcodes_reader_desc' => null,
			'bbcodes_size' => null,
			'bbcodes_size_desc' => null,
			'bbcodes_size_extended' => null,
			'bbcodes_size_large' => null,
			'bbcodes_size_small' => null,
			'bbcodes_size_title' => null,
			'bbcodes_sub' => null,
			'bbcodes_sub_desc' => null,
			'bbcodes_sup' => null,
			'bbcodes_sup_desc' => null,
			'bbcodes_table' => null,
			'bbcodes_table_desc' => null,
			'bbcodes_tt' => null,
			'bbcodes_tt_desc' => null,
			'bbcodes_underline' => null,
			'bbcodes_underline_desc' => null,
			'bbcodes_url' => null,
			'bbcodes_url_desc' => null,
			'bbcodes_url_prompt1' => null,
			'bbcodes_url_promtp2' => null,
			'bbcode_help_overview' => null,
			'bbcode_help_smileys' => null,
			'bbcode_help_smileys_desc' => null,
			'bbhelp_title' => null,
			'timezone_0' => null,
			'timezone_n1' => null,
			'timezone_n2' => null,
			'timezone_n3' => null,
			'timezone_n4' => null,
			'timezone_n5' => null,
			'timezone_n6' => null,
			'timezone_n7' => null,
			'timezone_n8' => null,
			'timezone_n9' => null,
			'timezone_n10' => null,
			'timezone_n11' => null,
			'timezone_n12' => null,
			'timezone_n35' => null,
			'timezone_p1' => null,
			'timezone_p2' => null,
			'timezone_p3' => null,
			'timezone_p4' => null,
			'timezone_p5' => null,
			'timezone_p6' => null,
			'timezone_p7' => null,
			'timezone_p8' => null,
			'timezone_p9' => null,
			'timezone_p10' => null,
			'timezone_p11' => null,
			'timezone_p12' => null,
			'timezone_p35' => null,
			'timezone_p45' => null,
			'timezone_p55' => null,
			'timezone_p65' => null,
			'timezone_p95' => null,
			'timezone_p575' => null,
			'showtopic_options_word' => null,
			'banned_head' => 'Access denied: You are banned!',
			'banned_left_never' => 'Never',
			'banned_no_reason' => 'No reason specified.',
			'bot_banned' => 'User-Agent or IP-Address is equal to one that is used by spam bots or e-mail collectors.',
			'error_no_forums_found' => 'There are currently no forums to show. Please visit the <a href="admin.php">Admin Control Panel</a> to create forums.',
			'post_report' => 'Report',
			'post_reported' => 'Reported Post',
			'report_message' => 'Message:',
			'report_message_desc' => 'Note: You should only report a post if there is a violation of our guidelines.',
			'report_post' => 'Report Post',
			'report_post_locked' => 'This post has been reported already and will be checked as soon as possible.',
			'report_post_success' => 'Thanks for your message. The moderators and administrators have been informed and we will check the post as soon as possible.',
			'spellcheck_disabled' => 'Spellcheck is disabled.',
			'banned_reason' => 'You have been banned for the following reason:',
			'banned_until' => 'Date the ban will be lifted: ',
			'admin_report' => 'Reported Post',
			'admin_report_not_found' => 'This post has been checked and has been set as done.',
			'admin_report_reset' => 'Set as done:',
			'admin_report_reset_success' => 'The reporst has been set as done.',
			'board_rules' => 'Forum Guidelines',
			'no_board_rules_specified' => 'No forum guidelines specified.',
			'why_register_desc' => 'In order to login you must be registered. Registering takes only a few seconds but gives you increased access.  The board administrator may also grant additional permissions to registered users. Before you login please ensure you are familiar with our terms of use and related policies. Please ensure you heed the forum guidelines as you use the forum.',
			'you_had_to_accept_agb' => 'You have to accept the forum guidelines!'
		),
		'language_de' => array(
			'bbcodes_align' => null,
			'bbcodes_align_center' => null,
			'bbcodes_align_desc' => null,
			'bbcodes_align_justify' => null,
			'bbcodes_align_left' => null,
			'bbcodes_align_right' => null,
			'bbcodes_align_title' => null,
			'bbcodes_bold' => null,
			'bbcodes_bold_desc' => null,
			'bbcodes_code' => null,
			'bbcodes_code_desc' => null,
			'bbcodes_color' => null,
			'bbcodes_color_desc' => null,
			'bbcodes_color_title' => null,
			'bbcodes_edit' => null,
			'bbcodes_edit_desc' => null,
			'bbcodes_email' => null,
			'bbcodes_email_desc' => null,
			'bbcodes_example_text' => null,
			'bbcodes_example_text2' => null,
			'bbcodes_expand' => null,
			'bbcodes_header' => null,
			'bbcodes_header_desc' => null,
			'bbcodes_header_h1' => null,
			'bbcodes_header_h2' => null,
			'bbcodes_header_h3' => null,
			'bbcodes_header_title' => null,
			'bbcodes_help' => null,
			'bbcodes_help_example' => null,
			'bbcodes_help_output' => null,
			'bbcodes_help_syntax' => null,
			'bbcodes_hide' => null,
			'bbcodes_hide_desc' => null,
			'bbcodes_hr' => null,
			'bbcodes_hr_desc' => null,
			'bbcodes_img' => null,
			'bbcodes_img_desc' => null,
			'bbcodes_italic' => null,
			'bbcodes_italic_desc' => null,
			'bbcodes_list' => null,
			'bbcodes_list_desc' => null,
			'bbcodes_list_ol' => null,
			'bbcodes_note' => null,
			'bbcodes_note_desc' => null,
			'bbcodes_note_prompt1' => null,
			'bbcodes_note_prompt2' => null,
			'bbcodes_option' => null,
			'bbcodes_ot' => null,
			'bbcodes_ot_desc' => null,
			'bbcodes_param' => null,
			'bbcodes_quote' => null,
			'bbcodes_quote_desc' => null,
			'bbcodes_reader' => null,
			'bbcodes_reader_desc' => null,
			'bbcodes_size' => null,
			'bbcodes_size_desc' => null,
			'bbcodes_size_extended' => null,
			'bbcodes_size_large' => null,
			'bbcodes_size_small' => null,
			'bbcodes_size_title' => null,
			'bbcodes_sub' => null,
			'bbcodes_sub_desc' => null,
			'bbcodes_sup' => null,
			'bbcodes_sup_desc' => null,
			'bbcodes_table' => null,
			'bbcodes_table_desc' => null,
			'bbcodes_tt' => null,
			'bbcodes_tt_desc' => null,
			'bbcodes_underline' => null,
			'bbcodes_underline_desc' => null,
			'bbcodes_url' => null,
			'bbcodes_url_desc' => null,
			'bbcodes_url_prompt1' => null,
			'bbcodes_url_promtp2' => null,
			'bbcode_help_overview' => null,
			'bbcode_help_smileys' => null,
			'bbcode_help_smileys_desc' => null,
			'bbhelp_title' => null,
			'timezone_0' => null,
			'timezone_n1' => null,
			'timezone_n2' => null,
			'timezone_n3' => null,
			'timezone_n4' => null,
			'timezone_n5' => null,
			'timezone_n6' => null,
			'timezone_n7' => null,
			'timezone_n8' => null,
			'timezone_n9' => null,
			'timezone_n10' => null,
			'timezone_n11' => null,
			'timezone_n12' => null,
			'timezone_n35' => null,
			'timezone_p1' => null,
			'timezone_p2' => null,
			'timezone_p3' => null,
			'timezone_p4' => null,
			'timezone_p5' => null,
			'timezone_p6' => null,
			'timezone_p7' => null,
			'timezone_p8' => null,
			'timezone_p9' => null,
			'timezone_p10' => null,
			'timezone_p11' => null,
			'timezone_p12' => null,
			'timezone_p35' => null,
			'timezone_p45' => null,
			'timezone_p55' => null,
			'timezone_p65' => null,
			'timezone_p95' => null,
			'timezone_p575' => null,
			'showtopic_options_word' => null,
			'banned_head' => 'Zugriff verweigert: Sie wurden gesperrt!',
			'banned_left_never' => 'Niemals',
			'banned_no_reason' => 'Keine Begründung angegeben.',
			'bot_banned' => 'User-Agent oder IP-Adresse entspricht einem bekannten Spam-Bot oder E-Mail-Sammler.',
			'error_no_forums_found' => 'Es wurde keine anzuzeigenden Foren gefunden. Sie können in der <a href="admin.php">Administration</a> neue Foren erstellen.',
			'post_report' => 'Melden',
			'post_reported' => 'Gemeldeter Beitrag',
			'report_message' => 'Nachricht:',
			'report_message_desc' => 'Hinweis: Ein Beitrag sollte nur dann gemeldet werden, wenn ein Verstoß gegen unsere Regeln vorliegt.',
			'report_post' => 'Beitrag melden',
			'report_post_locked' => 'Dieser Beitrag wurde bereits gemeldet und wird bald geprüft.',
			'report_post_success' => 'Danke für Ihre Nachricht. Die Moderatoren und Administratoren wurden verständigt und es wird sich in Kürze jemand darum kümmern.',
			'spellcheck_disabled' => 'Rechtschreibprüfung wurde deaktiviert.',
			'banned_reason' => 'Sie wurden aus folgendem Grund gesperrt:',
			'banned_until' => 'Ende der Sperre: ',
			'admin_report' => 'Gemeldeter Beitrag',
			'admin_report_not_found' => 'Dieser Beitrag wurde bereits überprüft und als erledigt markiert.',
			'admin_report_reset' => 'Als erledigt markieren:',
			'admin_report_reset_success' => 'Die Meldung wurde als erledigt markiert.'
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
			if ($text === null) {
				$c->delete($varname);
			}
			else {
				$c->updateconfig($varname, str, $text);
			}
		}
		$c->savedata();
	}
}

echo "- Language files updated.<br />";

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