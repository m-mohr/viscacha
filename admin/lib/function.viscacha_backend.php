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
	check_executable_r('smilies');
	check_executable_r('templates');
	check_executable_r('components');
	check_executable_r('language');
	check_executable('classes/cron/jobs');
	check_executable('classes/feedcreator');
	check_executable('classes/fonts');
	check_executable('classes/geshi');
	check_executable('classes/graphic/noises');
}

@ini_set('default_charset', '');
header('Content-type: text/html; charset: iso-8859-1');

$htmlhead = '';
$months = array('January','February','March','April','May','June','July','August','September','October','November','December');
$days = array('Sonntag','Montag','Dienstag','Mittwoch','Donenrstag','Freitag','Samstag');

// A simple caching class for Arrays etc.
include_once ("classes/function.cache.php");
// Variables
require_once ("classes/class.gpc.php");
$gpc = new GPC();
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
// BB-Code functions
include_once ("classes/class.bbcode.php");
// Global functions
require_once ("classes/function.global.php");

function wysiwyg($name, $wysiwyg = true, $preload = '<div class="border"><div class="h3">Title</div><div class="bbody">Content</div></div>', $inline = 1, $formid = 'form') {
	global $my, $tpl;
	$path = $tpl->altdir.'docs/'.$preload.'.html';
	if (strpos($preload, '<') === false && file_exists($path)) {
		$preload = file_get_contents($path);
	}
	$textarea = <<<EOD
<textarea id="{$name}" name="{$name}" rows="20" cols="110" class="texteditor">{$preload}</textarea>
EOD;
	if ($wysiwyg == true) {
		$field = <<<EOD
{$textarea}
<link rel="stylesheet" type="text/css" href="templates/editor/rte.css" />
<script language="JavaScript" type="text/javascript" src="templates/editor/lang/en.js"></script>
<script language="JavaScript" type="text/javascript" src="templates/editor/richtext.js"></script>
<script language="JavaScript" type="text/javascript" src="templates/editor/html2xhtml.js"></script>
<script language="JavaScript" type="text/javascript">
<!--
window.onload = function() {
	forms = FetchElement('{$formid}');
	ta = FetchElement('{$name}');
	forms.onsubmit = function() {
   		updateRTE('rte'); 
  		ta.value = forms.rte.value;
  		forms.submit(); 
	};
	ta.style.display = 'none';
};
var lang = "en";
var encoding = "iso-8859-1";
initRTE("templates/editor/images/", "templates/editor/", "designs/{$my->cssid}/standard.css", false);
writeRichText('rte', FetchElement('{$name}').value, '', 750, 350, true, false, false);
//-->
</script>
EOD;
		return $field;
	}
	else {
		return $textarea;
	}
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

function get_remote($file) {
	if (!preg_match('/^(http:\/\/)([\wäöüÄÖÜ@\-_\.]+)\:?([0-9]*)\/(.*)$/', $file, $url_ary)) {
		return false;
	}

	$base_get = '/' . $url_ary[4];
	$port = (!empty($url_ary[3])) ? $url_ary[3] : 80;

	if (!($fsock = @fsockopen($url_ary[2], $port, $errno, $errstr, 5))) {
		return false;
	}

	@fputs($fsock, "GET {$base_get} HTTP/1.1\r\n");
	@fputs($fsock, "HOST: " . $url_ary[2] . "\r\n");
	@fputs($fsock, "Connection: close\r\n\r\n");

	$data = '';
	while(!@feof($fsock)) {
		$data .= @fread($fsock, 1024);
	}
	@fclose($fsock);
		
	list($header,$data) = explode("\r\n\r\n", $data, 2);
	if (preg_match('#Location\: ([^\s]+)[\s]+#i', $header, $loc)) {
		$data = get_remote($loc[1]);
	}

	return $data;
}

function checkRemotePic($pic, $url_ary, $id) {
	global $config, $filesystem;
	if ($config['avwidth'] == 0) {
		$config['avwidth'] = 2048;
	}
	if ($config['avheight'] == 0) {
		$config['avheight'] = 2048;
	}
	if (empty($url_ary[4])) {
		error("admin.php?action=members&job=edit&id=".$id, 'Keine gültige URL angegeben.');
	}

	$base_get = '/' . $url_ary[4];
	$port = (!empty($url_ary[3])) ? $url_ary[3] : 80;

	if (!($fsock = @fsockopen($url_ary[2], $port, $errno, $errstr, 15))) {
		error("admin.php?action=members&job=edit&id=".$id, "Konnte keine Verbindung zum Avatar-Server herstellen:<br />{errno}: {$errstr}");
	}

	@fputs($fsock, "GET {$base_get} HTTP/1.1\r\n");
	@fputs($fsock, "HOST: " . $url_ary[2] . "\r\n");
	@fputs($fsock, "Connection: close\r\n\r\n");

	$avatar_data = '';
	while(!@feof($fsock)) {
		$avatar_data .= @fread($fsock, $config['avfilesize']);
	}
	@fclose($fsock);

	if (!preg_match('#Content-Length\: ([0-9]+)[^ /][\s]+#i', $avatar_data, $file_data1) || !preg_match('#Content-Type\: image/[x\-]*([a-z]+)[\s]+#i', $avatar_data, $file_data2)) {
		error("admin.php?action=members&job=edit&id=".$id, 'Es wurden keine gültige Rückgabe vom Avatar-Server empfangen.');
	}
		
	list(,$avatar_data) = explode("\r\n\r\n", $avatar_data, 2);
		
	$ext = get_extension($pic);
	$filename = md5(uniqid($id));
	$origfile = 'temp/'.$filename.$ext;
	$filesystem->file_put_contents($origfile, $avatar_data);
    $filesize = filesize($origfile);
    list($width, $height, $type) = @getimagesize($origfile);
    $types = explode('|', $config['avfiletypes']);

	if ($width > 0 && $height > 0 && $width <= $config['avwidth'] && $height <= $config['avheight'] && $filesize <= $config['avfilesize'] && in_array($ext, $types)) {
		$pic = 'uploads/pics/'.$id.$ext;
		removeOldImages('uploads/pics/', $id);
		$filesystem->copy($origfile, $pic);
	}
	else {
		error("admin.php?action=members&job=edit&id=".$id, 'Bild entspricht nicht den Vorgaben!');
		$filesystem->unlink($origfile);
	}
	return $pic;
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

function ini_maxupload() {
    $keys = array(
    'post_max_size' => 0,
    'upload_max_filesize' => 0
    );
    foreach ($keys as $key => $bytes) {
        $val = trim(@ini_get($key));
        $last = strtolower($val{strlen($val)-1});
        switch($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
        $keys[$key] = $val;
    }
    return min($keys);
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
    if($byte>1024) {
        $byte/=1024;
        $string = 'KB';
    }
    if($byte>1024) {
        $byte/=1024;
        $string = 'MB';
    }
    if($byte>1024) {
        $byte/=1024;
        $string = 'GB';
    }
    if($byte>1024) {
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
	<title><?php echo $config['fname']; ?>: Administrations Control Panel - powered by Viscacha</title>
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
        <strong><a href="http://www.mamo-net.de" target="_blank">Viscacha <?php echo $config['version']; ?></a></strong><br />
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
window.setTimeout('location.href="<?php echo $url; ?>"', 3000);
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
		return '<img'.$js.$id.' class="valign" src="admin/html/images/yes.gif" border="0" alt="Yes" title="Click here to change setting!">';
	}
	else {
		return '<img'.$js.$id.' class="valign" src="admin/html/images/no.gif" border="0" alt="No" title="Click here to change setting!">';
	}

}

function AdminSelectForum($tree, $cat, $board, $char = '&nbsp;&nbsp;', $level = 0) {
	foreach ($tree as $cid => $boards) {
		$cdata = $cat[$cid];
		?>
		<option style="background-color: #000000; color: #ffffff;" value="c_<?php echo $cdata['id']; ?>">
		<?php echo str_repeat($char, $level).$cdata['name']; ?>
		</option>
		<?php
		foreach ($boards as $bid => $sub) {
			$bdata = $board[$bid];
			if ($bdata['opt'] == 're') {
				continue;
			}
			?>
			<option value="f_<?php echo $bdata['id']; ?>">
			<?php echo str_repeat($char, $level+1).'+&nbsp;'.$bdata['name']; ?>
			</option>
	    	<?php
	    	AdminSelectForum($sub, $cat, $board, $char, $level+2);
	    }
	}
}
?>
