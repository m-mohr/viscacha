<?php
if (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == "function.viscacha_backend.php") die('Error: Hacking Attempt');

// Gets a file with php-functions
@include_once("classes/function.phpcore.php");
require_once("classes/class.filesystem.php");
$filesystem = new filesystem($config['ftp_server'], $config['ftp_user'], $config['ftp_pw'], $config['ftp_port']);
$filesystem->set_wd($config['ftp_path']);
@include_once("classes/function.chmod.php");
if ($config['check_filesystem'] == 1) {
	check_writable('admin/data/notes.php');
	check_writable_r('docs');
	check_writable_r('language');
	check_executable_r('admin/backup');
	check_executable_r('admin/data');
	check_executable_r('designs');
	check_executable_r('docs');
	check_executable_r('images');
	check_executable_r('templates');
	check_executable_r('components');
	check_executable_r('language');
	check_executable('classes/cron/jobs');
	check_executable('classes/feedcreator');
	check_executable('classes/fonts');
	check_executable('classes/geshi');
	check_executable('classes/graphic/noises');
	check_writable_r('templates');
}

@ini_set('default_charset', '');
header('Content-type: text/html; charset: iso-8859-1');

$htmlhead = '';

// Arrays for Dates
$months = array('January','February','March','April','May','June','July','August','September','October','November','December');
$days = array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');

// Arrays for Permissions
$gls = array(
	'admin' => 'Is Administrator',
	'gmod' => 'Is Global Moderator',
	'guest' => 'Is Guest',
	'members' => 'Can view Memberlist',
	'profile' => 'Can view Profiles',
	'pdf' => 'Can view PDF-Files',
	'pm' => 'Can use PM',
	'wwo' => 'Can view Who is Online',
	'search' => 'Can use Search',
	'team' => 'Can view Teamlist',
	'usepic' => 'Can use (own) Avatar',
	'useabout' => 'Create (own) Personal Page',
	'usesignature' => 'Can use (own) Signature',
	'downloadfiles' => 'Can download Attachements',
	'forum' => 'Can view Forums',
	'posttopics' => 'Can start a new Thread',
	'postreplies' => 'Can write a reply',
	'addvotes' => 'Can start a Poll',
	'attachments' => 'Can add Attachements',
	'edit' => 'Can edit own Posts',
	'voting' => 'Can vote',
	'docs' => 'Can view Documents/Pages'
);
$gll = array(
	'admin' => 'The user ist he highest ranked Administrator in the forum. He may use this admincenter and has full control of the forum!',
	'gmod' => 'The user will automatically be moderator in all forums and can use all options and actions on topics.',
	'guest' => 'The users in this usergroup are (not registered) guests.',
	'members' => 'May view the memberlist and use eventually observably data.',
	'profile' => 'The user may view the profiles of the members and use eventually observably data.',
	'pdf' => 'The user may download particular topics as PDF-file.',
	'pm' => 'The user may use the Private Messaging (PM) System. He can send, receive, administer and archive private messages.',
	'wwo' => 'May view the where-is-who-online-list with the users residence.',
	'search' => 'May use the Search and view the results.',
	'team' => 'May view the teamlist with administrators, global moderators and moderators.',
	'usepic' => 'May upload his own picture for his profile (frequently named avatar) or indicate an URL to a picture.',
	'useabout' => 'May create a personal site in his user profile.',
	'usesignature' => 'The user may create his own signature.',
	'downloadfiles' => 'The user may view and download attached files.',
	'forum' => 'The user may generally view the forums and read them.',
	'posttopics' => 'New topics may be started.',
	'postreplies' => 'Answers to topics may be written.',
	'addvotes' => 'Polls may be created within topics.',
	'attachments' => 'The user may attach files to his post.',
	'edit' => 'The user may edit and delete his own posts.',
	'voting' => 'The user may participate in polls in topics.',
	'docs' => 'May view all documents &amp; pages.'
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

// Variables
require_once ("classes/function.gpc.php");
$action = $gpc->get('action', none);
if (empty($_REQUEST['page'])) {
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

function AdminLogInForm() {
	?>
	<form action="admin.php?action=login2" method="post" target="_top">
	 <table class="border" style="width: 50%; margin: auto;">
	  <tr> 
	   <td class="obox" colspan="2">Log in</td>
	  </tr>
	  <tr> 
		<td class="mbox" width="40%">User Name:</td>
		<td class="mbox" width="60%"><input type="text" name="name" size="40" /></td>
	  </tr>
	  <tr> 
		<td class="mbox" width="40%">Password:</td>
		<td class="mbox" width="60%"><input type="password" name="pw" size="40" /></td>
	  </tr>
	  <tr> 
	   <td class="ubox" align="center" colspan="2"><input type="submit" value="Log in" /></td>
	  </tr>
	 </table>
	</form>
	<?php
}

function isInvisibleHook($hook) {
	switch ($hook) {
		case 'uninstall':
		case 'install':
		case 'source':
			return true;
		break;
		default:
			return false;
	}
}

function makeOSPath($array) {
	$dir = implode(DIRECTORY_SEPARATOR, $array);
	if (is_dir($dir)) {
		$dir .= DIRECTORY_SEPARATOR;
	}
	return $dir;
}

function pluginSettingGroupUninstall($pluginid) {
	global $db;
	$result = $db->query("SELECT id, name FROM {$db->pre}settings_groups WHERE name = 'module_{$pluginid}' LIMIT 1");
	$row = $db->fetch_assoc($result);
	
	$c = new manageconfig();
	$c->getdata();
	$result = $db->query("SELECT name FROM {$db->pre}settings WHERE sgroup = '{$row['id']}'");
	while ($row2 = $db->fetch_assoc($result)) {
		$c->delete(array($row['name'], $row2['name']));
	}
	$c->savedata();
	
	$db->query("DELETE FROM {$db->pre}settings WHERE sgroup = '{$row['id']}'", __LINE__, __FILE__);
	$db->query("DELETE FROM {$db->pre}settings_groups WHERE id = '{$row['id']}'", __LINE__, __FILE__);
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

function array2sqlsetlist($array, $seperator = ', ') {
	$sqlarray = array();
	foreach ($array as $key => $value) {
		$sqlarray[] = "`{$key}` = '{$value}'";
	}
	return implode($seperator, $sqlarray);
}

function gzAbortNotLoaded() {
	if (!extension_loaded("zlib") || !function_exists('readgzfile')) {
		error('javascript:history.back(-1);', 'GZIP Extension not loaded.');	
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
		$webserver = 'Unknown';
	}
	return $webserver;
}

function fileAge($age) {
    if($age>=30*24*60*60) {
        $age/=(30*24*60*60);
        $string = 'Monate';
    }
    elseif($age>=24*60*60) {
        $age/=(24*60*60);
        $string = 'Tage';
    }
    elseif($age>=60*60) {
        $age/=(60*60);
        $string = 'Std.';
    }
    elseif($age>=60) {
        $age/=60;
        $string = 'Min.';
    }
	else {
		$string = 'Sek.';
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
	global $gpc;

	$page = $gpc->get('page', int, 1);
	
	if ($anzposts == 0) {
		$anzposts = 1;
	}

   	$pgs = $anzposts/$teiler;
    $anz = ceil($pgs);
	
	$p = "Pages ($anz):";
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


function txt2img ($text,$op=NULL) {
	$imgtag = '<img src="classes/graphic/text2image.php?text='.rawurlencode($text).'&amp;angle=90" border="0">';
	if ($op == NULL) {
		echo $imgtag;
	}
	else {
		return $imgtag;
	}
}

function head($onload = '') {
	global $htmlhead, $config;
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
	<link rel="copyright" href="http://www.mamo-net.de">
	<script type="text/javascript">
	<!--
		var sidx = '<?php echo SID2URL_JS_x; ?>';
		var sid1 = '<?php echo SID2URL_JS_1; ?>';
	-->
	</script>
	<script src="admin/html/admin.js" language="Javascript" type="text/javascript"></script>
	<?php echo $htmlhead; ?>
</head>
<body<?php echo iif(!empty($onload), ' '.$onload); ?>>
	<?php
}
function foot() {
	global $config, $benchmark, $db;
	$benchmark = benchmarktime()-$benchmark;
	?>
	<br style="line-height: 8px;" />
	<div class="stext center">[Load Time: <?php echo round($benchmark, 5); ?>] [Queries: <?php echo $db->benchmark('queries'); ?>]</div>
    <div id="copyright">
        <strong><a href="http://www.viscacha.org" target="_blank">Viscacha <?php echo $config['version']; ?></a></strong><br />
        Copyright &copy; 2004-2006, MaMo Net
        <?php echo iif($config['pccron'] == 1, '<img src="cron.php" width="0" height="0" alt="" />'); ?>
    </div>
    </body>
    </html>
	<?php
}



function error ($errorurl, $errormsg='An unexpected error occurred') {
	global $config, $my, $db;
	if (!is_array($errormsg)) {
	    $errormsg = array($errormsg);
	}
	?>
<script language="Javascript" type="text/javascript">
<!--
window.setTimeout('location.href="<?php echo $errorurl; ?>"', 10000);
-->
</script>
<table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
    <td class="obox">An error occured:</td>
  </tr>
  <tr>
    <td class="mbox">
      Error message:<br />
      <ul>
        <?php foreach ($errormsg as $error) { ?>
        <li><?php echo $error; ?></li>
        <?php } ?>
      </ul>
      <p align="center" class="stext"><a href="<?php echo $errorurl; ?>">back</a></p>
    </td>
  </tr>
</table>
	<?php
	echo foot();
	$db->close();
	exit;
}

function ok ($url, $msg = "Settings were saved successfully!") {
	global $config, $my, $db;
	?>
<script language="Javascript" type="text/javascript">
<!--
window.setTimeout('location.href="<?php echo $url; ?>"', 1000);
-->
</script>
<table class="border" border="0" cellspacing="0" cellpadding="4" align="center">
  <tr>
    <td class="obox">Confirmation:</td>
  </tr>
  <tr>
    <td class="mbox" align="center">
    <?php echo $msg; ?><br /><br />
    <span class="stext"><a href="<?php echo $url; ?>">continue</a></span>
    </td>
  </tr>
</table>
	<?php
	echo foot();
	$db->close();
	exit;
}

function noki ($int, $js = '', $id = '') {
	if (!empty($id)) {
		$id = ' id="'.$id.'"';
	}
	if ($int == 1 || $int == true) {
		return '<img'.$js.$id.' class="valign" src="admin/html/images/yes.gif" border="0" alt="Yes"'.iif(!empty($js), ' title="Click here to change setting!"', ' title="Yes"').' />';
	}
	else {
		return '<img'.$js.$id.' class="valign" src="admin/html/images/no.gif" border="0" alt="No"'.iif(!empty($js), ' title="Click here to change setting!"', ' title="No"').' />';
	}

}

define('ADMIN_SELECT_CATEGORIES', 2);
define('ADMIN_SELECT_FORUMS', 1);
define('ADMIN_SELECT_ALL', 0);

function SelectBoardStructure($name = 'id', $group = ADMIN_SELECT_ALL, $standard = null, $no_select = false) {
	global $scache;
	
	$forumtree = $scache->load('forumtree');
	$tree = $forumtree->get();
	
	$categories_obj = $scache->load('categories');
	$categories = $categories_obj->get();
	
	$catbid = $scache->load('cat_bid');
	$boards = $catbid->get();
	
	$tree2 = array();
	SelectBoardStructure_html(&$tree2, $tree, $categories, $boards, $group, $standard);
	$forums = iif($no_select == false, '<select name="'.$name.'" size="1">');
	$forums .= implode("\n", $tree2);
	$forums .= iif($no_select == false, '</select>');
	return $forums;
}

function SelectBoardStructure_html(&$html, $tree, $cat, $board, $group, $standard = null, $char = '&nbsp;&nbsp;', $level = 0) {
	foreach ($tree as $cid => $boards) {
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
			$bdata = $board[$bid];
			if ($bdata['opt'] == 're') {
				continue;
			}
			$i++;
			if ($group != ADMIN_SELECT_CATEGORIES) {
				$value = iif($group == ADMIN_SELECT_ALL, 'forums_').$bdata['id'];
				$html[] = '<option '.iif($standard != null && $standard == $value, ' selected="selected"').' value="'.$value.'">'.str_repeat($char, $level+1).$bdata['name'].'</option>';
			}
	    	SelectBoardStructure_html(&$html, $sub, $cat, $board, $group, $standard, $char, $level+2);
	    }
	    if ($group == ADMIN_SELECT_FORUMS && $i == 0) {
	    	$x = array_pop($html);
	    }
	}
}
?>
