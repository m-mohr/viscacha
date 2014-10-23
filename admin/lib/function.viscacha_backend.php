<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

// Gets a file with php-functions
@include_once("classes/function.phpcore.php");
// Debugging / Error Handling things
require_once("classes/function.errorhandler.php");
// A class for Languages
require_once("classes/class.language.php");
$lang = new lang();
// Filesystem
require_once("classes/class.filesystem.php");
$filesystem = new filesystem($config['ftp_server'], $config['ftp_user'], $config['ftp_pw'], $config['ftp_port']);
$filesystem->set_wd($config['ftp_path'], $config['fpath']);

@ini_set('default_charset', '');
header('Content-type: text/html; charset=iso-8859-1');

// Colours
$txt2img_fg = '204a87';
$txt2img_bg = '94B7DF';

$htmlhead = '';

define('IMPTYPE_PACKAGE', 1);
define('IMPTYPE_DESIGN', 2);
define('IMPTYPE_SMILEYPACK', 3);
define('IMPTYPE_LANGUAGE', 4);
define('IMPTYPE_BBCODE', 5);

// Database functions
require_once('classes/database/'.$config['dbsystem'].'.inc.php');
$db = new DB($config['host'], $config['dbuser'], $config['dbpw'], $config['database'], $config['dbprefix']);
$db->setPersistence($config['pconnect']);
// Variables
require_once ("classes/function.gpc.php");
$action = $gpc->get('action', none);
if (empty($_GET['page']) || $_REQUEST['page'] < 1) {
	$_REQUEST['page'] = 1;
}
// Permission and Logging Class
require_once ("classes/class.permissions.php");
// A class for Templates
include_once ("classes/class.template.php");
// A class for Languages
include_once ("classes/class.language.php");
// Global functions
require_once ("classes/function.global.php");

$benchmark = benchmarktime();

$slog = new slog();
$my = $slog->logged();
$lang->initAdmin($my->language);
$tpl = new tpl();
$slog->checkBan();
$my->p = $slog->Permissions();

$job = $gpc->get('job', str);

if (!isset($my->settings['admin_interface'])) {
	$my->settings['admin_interface'] = $admconfig['nav_interface'];
}

// Arrays for Dates
$months = array($lang->phrase('admin_months_january'),$lang->phrase('admin_months_february'),$lang->phrase('admin_months_march'),$lang->phrase('admin_months_april'),$lang->phrase('admin_months_may'),$lang->phrase('admin_months_june'),$lang->phrase('admin_months_july'),$lang->phrase('admin_months_august'),$lang->phrase('admin_months_september'),$lang->phrase('admin_months_october'),$lang->phrase('admin_months_november'),$lang->phrase('admin_months_december'));
$days = array($lang->phrase('admin_days_sunday'),$lang->phrase('admin_days_monday'),$lang->phrase('admin_days_tuesday'),$lang->phrase('admin_days_wednesday'),$lang->phrase('admin_days_thursday'),$lang->phrase('admin_days_friday'),$lang->phrase('admin_days_saturday'));

// Arrays for Permissions
$gls = array(
	'admin' => $lang->phrase('admin_gls_admin'),
	'gmod' => $lang->phrase('admin_gls_gmod'),
	'guest' => $lang->phrase('admin_gls_guest'),
	'members' => $lang->phrase('admin_gls_members'),
	'profile' => $lang->phrase('admin_gls_profile'),
	'pm' => $lang->phrase('admin_gls_pm'),
	'wwo' => $lang->phrase('admin_gls_wwo'),
	'search' => $lang->phrase('admin_gls_search'),
	'team' => $lang->phrase('admin_gls_team'),
	'usepic' => $lang->phrase('admin_gls_usepic'),
	'useabout' => $lang->phrase('admin_gls_useabout'),
	'usesignature' => $lang->phrase('admin_gls_usesignature'),
	'downloadfiles' => $lang->phrase('admin_gls_downloadfiles'),
	'forum' => $lang->phrase('admin_gls_forum'),
	'posttopics' => $lang->phrase('admin_gls_posttopics'),
	'postreplies' => $lang->phrase('admin_gls_postreplies'),
	'addvotes' => $lang->phrase('admin_gls_addvotes'),
	'attachments' => $lang->phrase('admin_gls_attachments'),
	'edit' => $lang->phrase('admin_gls_edit'),
	'voting' => $lang->phrase('admin_gls_voting')
);
$gll = array(
	'admin' => $lang->phrase('admin_gll_admin'),
	'gmod' => $lang->phrase('admin_gll_gmod'),
	'guest' => $lang->phrase('admin_gll_guest'),
	'members' => $lang->phrase('admin_gll_members'),
	'profile' => $lang->phrase('admin_gll_profile'),
	'pm' => $lang->phrase('admin_gll_pm'),
	'wwo' => $lang->phrase('admin_gll_wwo'),
	'search' => $lang->phrase('admin_gll_search'),
	'team' => $lang->phrase('admin_gll_team'),
	'usepic' => $lang->phrase('admin_gll_usepix'),
	'useabout' => $lang->phrase('admin_gll_useabout'),
	'usesignature' => $lang->phrase('admin_gll_usesignature'),
	'downloadfiles' => $lang->phrase('admin_gll_downloadfiles'),
	'forum' => $lang->phrase('admin_gll_forum'),
	'posttopics' => $lang->phrase('admin_gll_posttopics'),
	'postreplies' => $lang->phrase('admin_gll_postreplies'),
	'addvotes' => $lang->phrase('admin_gll_addvotes'),
	'attachments' => $lang->phrase('admin_gll_attachments'),
	'edit' => $lang->phrase('admin_gll_edit'),
	'voting' => $lang->phrase('admin_gll_voting')
);

$glk = array_keys($gls);
$glk_forums = array(
	'f_downloadfiles' => 'downloadfiles',
	'f_forum' => 'forum',
	'f_posttopics' => 'posttopics',
	'f_postreplies' => 'postreplies',
	'f_addvotes' => 'addvotes',
	'f_attachments' => 'attachments',
	'f_edit' => 'edit',
	'f_voting' => 'voting'
);
$guest_limitation = array('admin', 'gmod', 'pm', 'usepic', 'useabout', 'usesignature', 'voting', 'edit');

function getLangCodesByDir($dir) {
	$d = dir($dir);
	$codes = array();
	while (false !== ($entry = $d->read())) {
		if (preg_match('~^(\w{2})_?(\w{0,2})$~i', $entry, $code) && is_dir("{$dir}/{$entry}")) {
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
	$d->close();
	return $codes;
}

function getLangCodesByKeys($keys) {
	$codes = array();
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
	return $codes;
}

function nl2whitespace($str){
	return preg_replace("~(\r\n|\n|\r)~", " ", $str);
}

function AdminLogInForm() {
	global $gpc, $lang;
    $addr = $gpc->get('addr', none);
	?>
	<form action="admin.php?action=login2<?php echo iif(!empty($addr), '&amp;addr='.rawurlencode($addr)); ?>" method="post" target="_top">
	 <table class="border" style="width: 50%;">
	  <tr>
	   <td class="obox" colspan="2"><?php echo $lang->phrase('admin_login_title'); ?></td>
	  </tr>
	  <tr>
		<td class="mbox" width="40%"><?php echo $lang->phrase('admin_login_username'); ?></td>
		<td class="mbox" width="60%"><input type="text" name="name" size="40" /></td>
	  </tr>
	  <tr>
		<td class="mbox" width="40%"><?php echo $lang->phrase('admin_login_password'); ?></td>
		<td class="mbox" width="60%"><input type="password" name="pw" size="40" /></td>
	  </tr>
	  <tr>
	   <td class="ubox" align="center" colspan="2"><input type="submit" value="<?php echo $lang->phrase('admin_form_login'); ?>" /></td>
	  </tr>
	 </table>
	</form>
	<?php
}

function isInvisibleHook($hook) {
	switch ($hook) {
		case 'uninstall':
		case 'update_init':
		case 'update_finish':
		case 'install':
		case 'source':
			return true;
		break;
		default:
			return false;
	}
}

function getHookArray() {
	$data = file('admin/data/hooks.txt');
	$data = array_map('trim', $data);
	$hooks = array();
	$group = null;
	foreach ($data as $line) {
		if (empty($line)) {
			continue;
		}
		if ($line{0} != '-') {
			$hooks[$line] = array();
			$group = $line;
			continue;
		}
		if ($group != null && $line{0} == '-') {
			$hooks[$group][] = substr($line, 1);
		}
	}
	return $hooks;
}

function addHookToArray($hook, $file) {
	global $filesystem;
	$data = file('admin/data/hooks.txt');
	$data = array_map('trim', $data);
	$exists = array_search("-{$hook}", $data);
	if ($exists === false || $exists === null) {
		$result = array_search($file, $data);
		if ($result !== false && $result !== null) {
			$data[$result] = $data[$result]."\r\n-{$hook}";
		}
		else {
			$data[] = '';
			$data[] = $file;
			$data[] = "-{$hook}";
		}
		$filesystem->file_put_contents('admin/data/hooks.txt', implode("\r\n", $data));
		return true;
	}
	else {
		return false;
	}
}

function array2sqlsetlist($array, $seperator = ', ') {
	$sqlarray = array();
	foreach ($array as $key => $value) {
		$sqlarray[] = "`{$key}` = '{$value}'";
	}
	return implode($seperator, $sqlarray);
}

function gzAbortNotLoaded() {
	if (!extension_loaded("zlib") || !viscacha_function_exists('readgzfile')) {
		global $lang;
		error('javascript:history.back(-1);', $lang->phrase('admin_gzip_not_loaded'));
	}
}

function gzTempfile($file, $new = null) {
	global $filesystem;
	ob_start();
	readgzfile($file);
	$data = ob_get_contents();
	ob_end_clean();
	if (empty($new)) {
		$new = 'temp/'.md5(microtime()).'.enc.tar';
	}
	$filesystem->file_put_contents($new, $data);
	return $new;
}

function get_webserver() {
	global $lang;
	if (preg_match('#Apache/([0-9\.\s]+)#si', $_SERVER['SERVER_SOFTWARE'], $wsregs)) {
		$webserver = "Apache v$wsregs[1]";
	}
	elseif (preg_match('#Microsoft-IIS/([0-9\.]+)#siU', $_SERVER['SERVER_SOFTWARE'], $wsregs)) {
		$webserver = "IIS v$wsregs[1]";
	}
	elseif (preg_match('#Zeus/([0-9\.]+)#siU', $_SERVER['SERVER_SOFTWARE'], $wsregs)) {
		$webserver = "Zeus v$wsregs[1]";
	}
	elseif (strtoupper($_SERVER['SERVER_SOFTWARE']) == 'APACHE') {
		$webserver = 'Apache';
	}
	elseif (defined('SAPI_NAME')) {
		$webserver = SAPI_NAME;
	}
	else {
		$webserver = $lang->phrase('admin_server_unknown');
	}
	return $webserver;
}

function fileAge($age) {
	global $lang;
    if($age>=30*24*60*60) {
        $age/=(30*24*60*60);
        $string = $lang->phrase('admin_months_name');
    }
    elseif($age>=24*60*60) {
        $age/=(24*60*60);
        $string = $lang->phrase('admin_days_name');
    }
    elseif($age>=60*60) {
        $age/=(60*60);
        $string = $lang->phrase('admin_hours_name');
    }
    elseif($age>=60) {
        $age/=60;
        $string = $lang->phrase('admin_minutes_name');
    }
	else {
		$string = $lang->phrase('admin_seconds_name');
	}
	return round($age, 0).' '.$string;
}

function recur_dir($dir, $clevel = 0) {
	$dirlist = opendir($dir);
	$std = count(explode('/',$dir));
	$i = 0;
	$mod_array = array();
	while ($file = readdir ($dirlist)) {
		$i++;
		if ($file != '.' && $file != '..') {
			$newpath = $dir.'/'.$file;
			$level = explode('/',$newpath);
			if (is_dir($newpath)) {
				$mod_array[-100000+$i] = array(
						'path'=>$newpath,
						'name'=>end($level),
						'dir'=>true,
						'level'=>$clevel,
						'mod_time'=>filemtime($newpath),
						'content'=>recur_dir($newpath, $clevel+1));
			}
			else {
				$mod_array[$i] = array(
						'path'=>$newpath,
						'name'=>end($level),
						'dir'=>false,
						'level'=>$clevel,
						'mod_time'=>filemtime($newpath),
						'size'=>filesize($newpath));
				}
		}
	}
	closedir($dirlist);
	ksort($mod_array);
	return $mod_array;
}

function formatFilesize($byte) {
    $string = 'Byte';
    if($byte>=1024) {
        $byte/=1024;
        $string = 'KB';
    }
    if($byte>=1024) {
        $byte/=1024;
        $string = 'MB';
    }
    if($byte>=1024) {
        $byte/=1024;
        $string = 'GB';
    }
    if($byte>=1024) {
        $byte/=1024;
        $string = 'TB';
    }
	return round($byte, 2)." ".$string;
}

function count_dir($dir, $totalsize=0) {
	$dir = $dir.'/';
	$handle = opendir($dir);
	while ($file = readdir ($handle)) {
		if ($file == '.' || $file == '..') {
			continue;
		}
		if(is_dir($dir.$file)) {
			$totalsize = count_dir($dir.$file, $totalsize);
		}
		else {
			$totalsize++;
		}
	}
	closedir($handle);
	return $totalsize;
}

function pages ($anzposts, $uri, $teiler=50) {
	global $gpc, $lang;

	$page = $gpc->get('page', int, 1);

	if ($anzposts == 0) {
		$anzposts = 1;
	}

   	$pgs = $anzposts/$teiler;
    $anz = ceil($pgs);

    $lang->assign('anz', $anz);
	$p = $lang->phrase('admin_pages');
	for ($i = 1; $i <= $anz; $i++) {
		if ($page == $i) {
			$p .= " [<strong>$i</strong>]";
		}
		else {
			$p .= ' [<a href="'.$uri.'page='.$i.'">'.$i.'</a>]';
		}
	}
	return $p;
}

function head($onload = '') {
	global $htmlhead, $config, $my;
	?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<title><?php echo $config['fname']; ?>: Administration Control Panel - powered by Viscacha</title>
	<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1">
	<meta http-equiv="Pragma" content="no-cache" />
	<meta http-equiv="Expires" content="-1" />
	<meta http-equiv="Cache-Control" content="no-cache" />
	<link rel="stylesheet" type="text/css" href="admin/html/standard.css">
	<link rel="up" href="javascript:self.scrollTo(0,0);">
	<link rel="copyright" href="http://www.viscacha.org">
	<script src="templates/lang2js.php?id=<?php echo $my->language; ?>&amp;admin=1" type="text/javascript"></script>
	<script language="JavaScript" type="text/javascript"><!--
		var sidx = '<?php echo SID2URL_JS_x; ?>';
		var sid1 = '<?php echo SID2URL_JS_1; ?>';
	--></script>
	<script src="templates/global.js" language="Javascript" type="text/javascript"></script>
	<script src="admin/html/admin.js" language="Javascript" type="text/javascript"></script>
	<?php echo $htmlhead; ?>
</head>
<body<?php echo iif(!empty($onload), ' '.$onload); ?>>
	<?php
}
function foot($nocopy = false) {
	if ($nocopy == false) {
		global $config, $benchmark, $db, $lang;
		$benchmark = round(benchmarktime()-$benchmark, 5);
		$queries = $db->benchmark('queries');
		$lang->assign('queries', $queries);
		$lang->assign('benchmark', $benchmark);
		?>
		<br style="line-height: 8px;" />
		<div class="stext center">[<?php echo $lang->phrase('admin_benchmark_generation_time'); ?>] [<?php echo $lang->phrase('admin_benchmark_queries'); ?>]</div>
	    <div id="copyright">
	        Powered by <strong><a href="http://www.viscacha.org" target="_blank">Viscacha <?php echo $config['version']; ?></a></strong><br />
	        Copyright &copy; 2004-2009, The Viscacha Project
	        <?php echo iif($config['pccron'] == 1, '<img src="cron.php" width="0" height="0" alt="" />'); ?>
	    </div>
	<?php } ?>
    </body>
    </html>
	<?php
}

function error ($errorurl, $errormsg = null, $time = null) {
	global $config, $my, $db, $lang, $slog;
	if ($errormsg == null) {
		$errormsg = array($lang->phrase('admin_an_unexpected_error_occured'));
	}
	else if (!is_array($errormsg)) {
	    $errormsg = array($errormsg);
	}
	if (!is_int($time)) {
		$time = 7500 + count($errormsg)*2500;
	}
	?>
<script language="Javascript" type="text/javascript">
<!--
window.setTimeout('<?php echo JS_URL($errorurl); ?>', <?php echo $time; ?>);
-->
</script>
<table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
    <td class="obox"><?php echo $lang->phrase('admin_an_error_occured'); ?></td>
  </tr>
  <tr>
    <td class="mbox">
      <?php echo $lang->phrase('admin_error_message'); ?><br />
      <ul>
        <?php foreach ($errormsg as $error) { ?>
        <li><?php echo $error; ?></li>
        <?php } ?>
      </ul>
      <p align="center" class="stext"><a href="<?php echo $errorurl; ?>"><?php echo $lang->phrase('admin_back'); ?></a></p>
    </td>
  </tr>
</table>
	<?php
	echo foot();
	$slog->updatelogged();
	$db->close();
	exit;
}

function ok ($url, $msg = null, $time = 1500) {
	global $config, $my, $db, $lang, $slog;
	if ($msg == null) {
		$msg = $lang->phrase('admin_settings_successfully_saved');
	}
	?>
<script language="Javascript" type="text/javascript">
<!--
window.setTimeout('<?php echo JS_URL($url); ?>', <?php echo $time; ?>);
-->
</script>
<table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
    <td class="obox"><?php echo $lang->phrase('admin_confirmation'); ?></td>
  </tr>
  <tr>
    <td class="mbox" align="center">
    <?php echo $msg; ?><br /><br />
    <span class="stext"><a href="<?php echo $url; ?>"><?php echo $lang->phrase('admin_continue'); ?></a></span>
    </td>
  </tr>
</table>
	<?php
	echo foot();
	$slog->updatelogged();
	$db->close();
	exit;
}

function noki ($int, $js = '', $id = '') {
	global $lang;
	if (!empty($id)) {
		$id = ' id="'.$id.'"';
	}
	if ($int == 1 || $int == true) {
		return '<img'.$js.$id.' class="valign" src="admin/html/images/yes.gif" border="0" alt="'.$lang->phrase('admin_yes').'"'.iif(!empty($js), ' title="'.$lang->phrase('admin_click_here_to_change_setting').'"', ' title="'.$lang->phrase('admin_yes').'"').' />';
	}
	else {
		return '<img'.$js.$id.' class="valign" src="admin/html/images/no.gif" border="0" alt="'.$lang->phrase('admin_no').'"'.iif(!empty($js), ' title="'.$lang->phrase('admin_click_here_to_change_setting').'"', ' title="'.$lang->phrase('admin_no').'"').' />';
	}

}

define('ADMIN_SELECT_CATEGORIES', 2);
define('ADMIN_SELECT_FORUMS', 1);
define('ADMIN_SELECT_ALL', 0);

function SelectBoardStructure($name = 'id', $group = ADMIN_SELECT_ALL, $standard = null, $no_select = false, $skip = null) {
	global $scache;

	$forumtree = $scache->load('forumtree');
	$tree = $forumtree->get();

	$categories_obj = $scache->load('categories');
	$categories = $categories_obj->get();

	$catbid = $scache->load('cat_bid');
	$boards = $catbid->get();

	$tree2 = array();
	SelectBoardStructure_html($tree2, $tree, $categories, $boards, $group, $standard, $skip);
	$forums = iif($no_select == false, '<select name="'.$name.'" size="1">');
	$forums .= implode("\n", $tree2);
	$forums .= iif($no_select == false, '</select>');
	return $forums;
}

function SelectBoardStructure_html(&$html, $tree, $cat, $board, $group, $standard = null, $skip = null, $char = '&nbsp;&nbsp;', $level = 0) {
	if ($skip != null) {
		list($skipType, $skipId) = explode('_', $skip);
	}
	else {
		$skipId = $skipType = null;
	}

	foreach ($tree as $cid => $boards) {
		if ($skipId == $cid && $skipType == 'c') {
			continue;
		}
		$cdata = $cat[$cid];
		if ($group == ADMIN_SELECT_FORUMS) {
			$html[] = '<optgroup label="'.str_repeat($char, $level).$cdata['name'].'"></optgroup>';
		}
		else {
			$value = iif($group == ADMIN_SELECT_ALL, 'categories_').$cdata['id'];
			$html[] = '<option'.iif($group != ADMIN_SELECT_CATEGORIES, 'style="font-weight: bold;"').iif($standard != null && $standard == $value, ' selected="selected"').' value="'.$value.'">'.str_repeat($char, $level).$cdata['name'].'</option>';
		}
		$i = 0;
		foreach ($boards as $bid => $sub) {
			if ($skipId == $bid && $skipType == 'b') {
				continue;
			}
			$bdata = $board[$bid];
			if ($bdata['opt'] == 're') {
				continue;
			}
			$i++;
			if ($group != ADMIN_SELECT_CATEGORIES) {
				$value = iif($group == ADMIN_SELECT_ALL, 'forums_').$bdata['id'];
				$html[] = '<option '.iif($standard != null && $standard == $value, ' selected="selected"').' value="'.$value.'">'.str_repeat($char, $level+1).$bdata['name'].'</option>';
			}
	    	SelectBoardStructure_html($html, $sub, $cat, $board, $group, $standard, $skip, $char, $level+2);
	    }
	    if ($group == ADMIN_SELECT_FORUMS && $i == 0) {
	    	$x = array_pop($html);
	    }
	}
}
?>
