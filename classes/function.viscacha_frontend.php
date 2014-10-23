<?php
/*
	Viscacha - A bulletin board solution for easily managing your content
	Copyright (C) 2004-2006  Matthias Mohr, MaMo Net
	
	Author: Matthias Mohr
	Publisher: http://www.mamo-net.de
	Start Date: May 22, 2004

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if (isset($_SERVER['PHP_SELF']) && basename($_SERVER['PHP_SELF']) == "function.viscacha_frontend.php") die('Error: Hacking Attempt');

require_once("classes/function.frontend_init.php");

function DocCodePagination($cc) {
	$pos1 = stripos($cc, '{pagebreak}');
	if ($pos1 === false) {
		return array($cc, 1);
	}
	else {
		$page = $_GET['page']-1;
		$pgc = preg_split("~(<br[^>]*>|\n|\r)*\{pagebreak\}(<br[^>]*>|\n|\r)*~i", $cc, -1, PREG_SPLIT_NO_EMPTY);
		if (!isset($pgc[$page])) {
			$page = 0;
			$_GET['page'] = 1;
		}
		return array($pgc[$page], count($pgc));
	}
}

function DocCodeParser($syntax, $parser = 1) {
	if ($parser == 2) {
		ob_start();
		$code = str_replace('<'.'?php','<'.'?',$syntax);
		$code = '?'.'>'.trim($code).'<'.'?';
		extract($GLOBALS, EXTR_SKIP);
		eval($code);
		$syntax = ob_get_contents();
		ob_end_clean();
	}
	elseif ($parser == 3) {
		$bbcode = initBBCodes();
		$syntax = $bbcode->parse($syntax);
	}
	elseif ($parser == 0) {
		$syntax = htmlspecialchars($syntax, ENT_NOQUOTES);
	}
	return $syntax;
}

function GroupCheck($groups) {
    global $slog;
    if ($groups == 0 || count(array_intersect(explode(',', $groups), $slog->groups)) > 0) {
        return true;
    }
    else {
        return false;
    }
}

function checkRemotePic($pic, $url_ary, $id, $redir = "editprofile.php?action=pic") {
	global $lang, $config;
	$redir .= SID2URL_x;
	if (empty($url_ary[4])) {
		error($lang->phrase('editprofile_pic_error1'), $redir);
	}

	$base_get = '/' . $url_ary[4];
	$port = (!empty($url_ary[3])) ? $url_ary[3] : 80;

	if (!($fsock = @fsockopen($url_ary[2], $port, $errno, $errstr, 10))) {
		error($lang->phrase('editprofile_pic_error2'), $redir);
	}

	@fputs($fsock, "GET $base_get HTTP/1.1\r\n");
	@fputs($fsock, "HOST: " . $url_ary[2] . "\r\n");
	@fputs($fsock, "Connection: close\r\n\r\n");

	$avatar_data = '';
	while(!@feof($fsock)) {
		$avatar_data .= @fread($fsock, $config['avfilesize']);
	}
	@fclose($fsock);

	if (!preg_match('#Content-Length\: ([0-9]+)[^ /][\s]+#i', $avatar_data, $file_data1) || !preg_match('#Content-Type\: image/[x\-]*([a-z]+)[\s]+#i', $avatar_data, $file_data2)) {
		error($lang->phrase('editprofile_pic_error4'), $redír);
	}
		
	list(,$avatar_data) = explode("\r\n\r\n", $avatar_data, 2);
		
	$ext = get_extension($pic);
	$filename = md5(uniqid($id));
	$origfile = 'temp/'.$filename.$ext;
	file_put_contents($origfile, $avatar_data);
    $filesize = filesize($origfile);
    list($width, $height, $type) = @getimagesize($origfile);
    $types = explode('|', $config['avfiletypes']);

	if ($width > 0 && $height > 0 && $width <= $config['avwidth'] && $height <= $config['avheight'] && $filesize <= $config['avfilesize'] && in_array($ext, $types)) {
		$pic = 'uploads/pics/'.$id.$ext;
		removeOldImages('uploads/pics/', $id);
		@copy($origfile, $pic);
	}
	else {
		error($lang->phrase('editprofile_pic_error3'), $redir);
		@unlink($origfile);
	}
	return $pic;
}
function numbers ($nvar,$deci=NULL) {
	global $config, $lang;
	
	if ($nvar == '-') return $nvar;
	
	if (strstr($nvar,'.') == false) $deci = '0';
	else $deci = $config['decimals'];
	
	$var = number_format($nvar, $deci, $lang->phrase('decpoint'), $lang->phrase('thousandssep'));
	
	return $var;
}

function formatFilesize($byte) {
	global $lang;
    $string = $lang->phrase('fs_byte');
    if($byte>1024) {
        $byte/=1024;
        $string = $lang->phrase('fs_kb');
    }
    if($byte>1024) {
        $byte/=1024;
        $string = $lang->phrase('fs_mb');
    }
    if($byte>1024) {
        $byte/=1024;
        $string = $lang->phrase('fs_gb');
    }
    if($byte>1024) {
        $byte/=1024;
        $string = $lang->phrase('fs_tb');
    }

    if(numbers($byte) != $byte) {
    	$byte=numbers($byte);
    }
	return $byte." ".$string;
}

function show_feeds() {
	$data = file('data/feedcreator.inc.php');
	$data = array_map('trim', $data);
	foreach ($data as $feed) {
		$feed = explode("|", $feed);
		if ($feed[3] == 1 && file_exists('classes/feedcreator/'.$feed[1])) {
			$f[$feed[0]] = $feed[2];
		}
	}
	return $f;
}

function get_headboards($fc, $last, $returnids = FALSE) {
	global $breadcrumb;

	$headids = array($last['id']);
	$bc_cache = array();
	
	while ($last['bid'] > 0 && $fc[$last['bid']]['bid'] > -1) {
		$last = $fc[$last['bid']];
		$bc_cache[] = array(
			'title' => $last['name'],
			'url' => "showforum.php?id=".$last['id'].SID2URL_x
		);
		$headids[] = $last['id'];
	}
	$bc_cache = array_reverse($bc_cache);
	
	foreach ($bc_cache as $row) {
		$breadcrumb->Add($row['title'], $row['url']);
	}
	
	if ($returnids == TRUE) {
		return $headids;
	}
}

function count_nl($str='',$max=NULL) {
	if (empty($str)) {
		return 0;
	}
	else {
		preg_match_all("/\r\n|\r|\n/", $str, $treffer);
		$count = count($treffer[0]);
		if ($max == NULL) {
			return $count;
		}
		else {
			if ($max < $count) {
				return $max;
			}
			else {
				return $count;
			}
		}
	}
}

function get_mimetype($file) {
    global $db;

	$ext = strtolower(get_extension($file, TRUE));

    $scache = new scache('mimetype_headers');
    if ($scache->existsdata() == TRUE) {
        $mime = $scache->importdata();
    }
    else {
        $result = $db->query("SELECT extension, mimetype, stream FROM {$db->pre}filetypes WHERE mimetype != 'application/octet-stream' AND stream != 'attachment'",__LINE__,__FILE__);
        $mime = array();
        while ($row = $db->fetch_assoc($result)) {
        	$extensions = explode(',', $row['extension']);
			foreach ($extensions as $extension) {
            	$extension = strtolower($extension);
				$mime[$extension] = array(
				'mimetype' => $row['mimetype'],
				'stream' => $row['stream']
				);
            }
        }
        $scache->exportdata($mime);
    }
    
    if (isset($mime[$ext])) {
		return array(
		'mime' => $mime[$ext]['mimetype'],
		'browser' => $mime[$ext]['stream']
		);
	}
	else {
		return array(
		'mime' => 'application/octet-stream',
		'browser' => 'attachment'
		);
	}
}

// Params: Anz. Beiträge, Beiträge p. Seite (array-index in $config), URL

function pages ($anzposts, $arrindex, $uri) {
	global $config, $tpl, $lang;

	if ($anzposts == 0) {
		$anzposts = 1;
	}

   	$pgs = $anzposts/$config[$arrindex];
    $anz = ceil($pgs);
    $sep = $lang->phrase('pages_sep');
    $p = &$_GET['page'];
    $pages = array();
	if ($anz > 10) {
		$pages[1] = 1;
		$pages[2] = 2;
		$pages[$anz-1] = $anz-1;
		$pages[$anz] = $anz;
		if ($p >= 2 && $p <= $anz-1) {
		    $pages[$p-1] = $p-1;
		    $pages[$p+1] = $p+1;
		}
		if (!isset($pages[$p+2]) && $p+2 <= $anz) {
		    $pages[$p+2] = $sep;
		}
		if (!isset($pages[$p-2]) && $p-2 > 0) {
			$pages[$p-2] = $sep;
		}
	}
	else {
	    for($i=1;$i<=$anz;$i++) {
	        $pages[$i] = $i;
	    }
	}
	$tpl->globalvars(compact("p"));
	$pages[$p] = $tpl->parse("main/pages_current");

	ksort($pages);
	
	$tpl->globalvars(compact("uri", "anz", "pages"));
	$lang->assign('anz', $anz);
    return $tpl->parse("main/pages");
}

function double_udata ($opt,$val) {
	global $db;
	$result = $db->query('SELECT id FROM '.$db->pre.'user WHERE '.$opt.' = "'.$val.'" LIMIT 1',__LINE__,__FILE__);
	if ($db->num_rows($result) == 0) {
		return true;
	}
	else {
		return false;
	}
}

function t1 () {
	return benchmarktime();
}

function t2 ($time = NULL) {
	if ($time == NULL) {
		global $zeitmessung1;
	}
	else {
		$zeitmessung1 = $time;
	}
	$zeitmessung2=benchmarktime();
	
	$zeitmessung=$zeitmessung2-$zeitmessung1;
	$zeitmessung=substr($zeitmessung,0,7);
	
	return $zeitmessung;
}


function UpdateTopicStats ($topic,$givestat=0) {
	global $db;
	$resultc = $db->query("SELECT COUNT(*) FROM {$db->pre}replies WHERE topic_id='$topic' AND tstart = '0'",__LINE__,__FILE__);
	$count = $db->fetch_array($resultc);
	$result = $db->query("SELECT date, name FROM {$db->pre}replies WHERE topic_id='$topic' ORDER BY date DESC LIMIT 1",__LINE__,__FILE__);
	$info = $db->fetch_array($result);
	if ($givestat == 0) {
	    $db->query("UPDATE {$db->pre}topics SET posts = '{$count[0]}', last = '{$info['date']}', last_name = '{$info['name']}' WHERE id = '".$topic."'",__LINE__,__FILE__);
	}
	else {
	    return $count[0];
	}
}

function SubStats($rtopics, $rreplys, $rid, $cat_cache,$bids=array()) {
	if(isset($cat_cache[$rid])) {
		$sub  = $cat_cache[$rid];
		foreach ($sub as $subfs) {
		array_push($bids, $subfs['id']);
			if (isset($cat_cache[$subfs['id']])) {
				$substats = SubStats(0, 0, $subfs['id'], $cat_cache, $bids);
				$rtopics = $rtopics+$subfs['topics']+$substats[0];
				$rreplys = $rreplys+$subfs['replys']+$substats[1];
				$bids = $substats[2];
			}
			else {
				$rtopics = $rtopics+$subfs['topics'];
				$rreplys = $rreplys+$subfs['replys'];
			}
		}
	}
	return array($rtopics, $rreplys, $bids);
}


function BoardSelect($board = 0) {
	global $config, $my, $tpl, $db, $gpc, $lang;

	$found = FALSE;
	$sub_cache = array();
	$sub_cache_last = array();
	$cat_cache = array();
	$mod_cache = array();
	$forum_cache = array();
	$cat_cache = cache_categories();
	if (!isset($GLOBALS['memberdata']) || !is_array($GLOBALS['memberdata'])) {
		$memberdata = cache_memberdata();
	}
	else {
		$memberdata = $GLOBALS['memberdata'];
	}
	$scache = new scache('index-moderators');
	if ($scache->existsdata() == TRUE) {
	    $mod_cache = $scache->importdata();
	}
	else {
	    $result = $db->query('SELECT mid, bid FROM '.$db->pre.'moderators WHERE time > '.time().' OR time IS NULL',__LINE__,__FILE__);
	    $mod_cache = array();
	    while($row = $db->fetch_assoc($result)) {
	    	if (isset($memberdata[$row['mid']])) {
	    		$row['name'] = $memberdata[$row['mid']];
	    		$mod_cache[$row['bid']][] = $row;
	    	}
	    }
		$scache->exportdata($mod_cache);
	}

    // Fetch Forums
    $sql = "SELECT 
    c.id, c.name, c.desc, c.opt, c.optvalue, c.bid, c.topics, c.replys, c.cid, c.last_topic, 
    t.topic as btopic, t.id as btopic_id, t.last as bdate, u.name AS uname, t.last_name AS bname
    FROM {$db->pre}cat AS c
        LEFT JOIN {$db->pre}topics AS t ON c.last_topic=t.id 
        LEFT JOIN {$db->pre}user AS u ON t.last_name=u.id 
    ORDER BY c.cid, c.c_order, c.id";
	$result=$db->query($sql,__LINE__,__FILE__);
	if ($db->num_rows($result) == 0) {
		$errormsg = array('There are currently no boards to show. Pleas visit the <a href="admin.php'.SID2URL_1.'">Admin Control Panel</a> and create some forums.');
		$errorurl = '';
		$tpl->globalvars(compact("errorurl","errormsg"));
		echo $tpl->parse('main/error');
		return $found;
	} 

	while($row = $db->fetch_assoc($result)) {
		$gpc->prepare($row['name']);
		$gpc->prepare($row['btopic']);
		$gpc->prepare($row['uname']);
		$gpc->prepare($row['bname']);
	    // Caching for Subforums
	    if ($row['bid'] > 0) {
	        $sub_cache[$row['bid']][] = $row;
	        $sub_cache_last[$row['id']] = $row;
	    }
	    // Caching the Forums
	    if ($row['bid'] == $board) {
	        $forum_cache[$row['cid']][] = $row;
	    }
	}
	
	// Work with the chached data!
    foreach ($cat_cache as $cat) {
    	$forums = array();
        if (isset($forum_cache[$cat['id']]) == false) {
            continue;
        }
        foreach ($forum_cache[$cat['id']] as $forum) {
            $found = TRUE;
    		$forum['new'] = false;  
    		
            $forum['mbdate'] = $forum['bdate'];
    	    
    	    // Subforendaten vererben (Letzter Beitrag, Markierung)
    	    if(isset($sub_cache[$forum['id']])) {	
    			$substats = SubStats($forum['topics'], $forum['replys'], $forum['id'], $sub_cache);
    			$forum['topics'] = $substats[0];
    			$forum['replys'] = $substats[1];
    			$bids = $substats[2];
    		}
    
    		$last = $forum['last_topic'];
    		$last_date = $forum['bdate'];
    		if(isset($sub_cache[$forum['id']])) {
    			foreach ($bids as $bidf) {
    				$sub = $sub_cache_last[$bidf];
    				if ($last_date < $sub['bdate']) {
    					$last = $sub['id'];
    					$last_date = $sub['bdate'];
    				}
    			}
    		}

			$forum['lname'] = is_id($forum['bname']) ? $forum['uname'] : $forum['bname'];

    		if ($last != $forum['last_topic']) {
    			$forum['id2'] = $last;
    			$forum['last_topic'] = $sub_cache_last[$forum['id2']]['last_topic'];
    			$forum['btopic_id'] = $sub_cache_last[$forum['id2']]['btopic_id'];
    			$forum['btopic'] = $sub_cache_last[$forum['id2']]['btopic'];
    			$forum['bdate'] = $sub_cache_last[$forum['id2']]['bdate'];
    			if (!isset($sub_cache_last[$forum['id2']]['lname'])) {
    				$forum['lname'] = is_id($sub_cache_last[$forum['id2']]['bname']) ? $sub_cache_last[$forum['id2']]['uname'] : $sub_cache_last[$forum['id2']]['bname'];
    			}
    			else {
    				$forum['lname'] = $sub_cache_last[$forum['id2']]['lname'];
    			}
    		}
    		else {
    			$forum['id2'] = $forum['id'];
    		}
    		
    		$id = array_search(trim($forum['lname']), $memberdata);
    		if (is_id($id)) {
    			$forum['lname'] = array($forum['lname'], $id);
    		}
    		else {
    			$forum['lname'] = array($forum['lname'], 0);
    		}
    	
    		if ($forum['btopic_id']) {
    			$forum['tid'] = $forum['btopic_id'];
    		}
    		else {
    			$forum['tid'] = $forum['last_topic'];
    		}
    		
            // Rechte und Gelesensystem
    		if ($forum['opt'] != 're') {
    			if (!check_forumperm($forum)) {
					$forum['foldimg'] = $tpl->img('cat_locked');
    				$forum['topics'] = '-';
    				$forum['replys'] = '-';
    				$forum['btopic'] = FALSE;
    			}
    			else {
    				if ((isset($my->mark['f'][$forum['id']]) && $my->mark['f'][$forum['id']] > $forum['bdate']) || $forum['bdate'] < $my->clv || $forum['topics'] < 1) {
    					$forum['foldimg'] = $tpl->img('cat_open');
    				}
    				else {
    				   	$forum['foldimg'] = $tpl->img('cat_red');
    				   	$forum['new'] = true;
    				}
		    		if ($forum['btopic']) {
		    			if (strxlen($forum['btopic']) >= 40) {
		    				$forum['btopic'] = substr($forum['btopic'],0,40);
		    				$forum['btopic'] .= "...";
		    			}
		    			$forum['bdate'] = str_date($lang->phrase('dformat1'), times($forum['bdate']));
		    			
		    		}
    		    }
    	    }
    	    $forum['topics'] = numbers($forum['topics']);
    	    $forum['replys'] = numbers($forum['replys']);
    	    
    	    // Moderatoren
    	    $forum['mod'] = array();
    	    if(isset($mod_cache[$forum['id']])) {
				$anz2 = count($mod_cache[$forum['id']]);
				for($i = 0; $i < $anz2; $i++) {
					if ($anz2 != $i+1) {
						$mod_cache[$forum['id']][$i]['sep'] = ', ';
					}
					else {
						$mod_cache[$forum['id']][$i]['sep'] = '';
					}
				    $forum['mod'][] = $mod_cache[$forum['id']][$i];
				}
			}
    	    // Unterforen
    	    $forum['sub'] = array();
    	    if ($config['showsubfs']) {
				if(isset($sub_cache[$forum['id']])) {
					$anz2 = count($sub_cache[$forum['id']]);
					$sub = array();
					for($i = 0; $i < $anz2; $i++) { 
						$sub_cache[$forum['id']][$i]['new'] = false;
			    		if ($sub_cache[$forum['id']][$i]['opt'] != 're') {
			    			if (!check_forumperm($sub_cache[$forum['id']][$i])) {
								$sub_cache[$forum['id']][$i]['foldimg'] = $tpl->img('subcat_locked');
			    			}
			    			else {
			    				if ((isset($my->mark['f'][$sub_cache[$forum['id']][$i]['id']]) && $my->mark['f'][$sub_cache[$forum['id']][$i]['id']] > $sub_cache[$forum['id']][$i]['bdate']) || $sub_cache[$forum['id']][$i]['bdate'] < $my->clv || $sub_cache[$forum['id']][$i]['topics'] < 1) {
			    					$sub_cache[$forum['id']][$i]['foldimg'] = $tpl->img('subcat_open');
			    				}
			    				else {
			    				   	$sub_cache[$forum['id']][$i]['foldimg'] = $tpl->img('subcat_red');
			    				   	$sub_cache[$forum['id']][$i]['new'] = true;
			    				}
			    		    }
			    	    }
			    	    else {
			    	    	$sub_cache[$forum['id']][$i]['foldimg'] = $tpl->img('subcat_redirect');
			    	    }
						$forum['sub'][] = $sub_cache[$forum['id']][$i];
					}
				}
			}
            $forums[] = $forum;
        }
        $tpl->globalvars(compact("cat","forums"));
        echo $tpl->parse("categories");
    }
    return $found;
}


function GoBoardPW ($bpw, $bid) {
	global $my, $config, $tpl, $db, $slog, $phpdoc, $zeitmessung;
	if(!isset($my->pwfaccess[$bid]) || $my->pwfaccess[$bid] != $bpw) {
        echo $tpl->parse("main/boardpw");
		$slog->updatelogged();
		$zeitmessung = t2();
		echo $tpl->parse("footer");
		$phpdoc->Out();
		$db->close();
		exit;
	}
}

function errorLogin($errormsg=NULL,$errorurl=NULL,$EOS = NULL) {
	global $config, $my, $tpl, $zeitmessung, $db, $slog, $phpdoc, $lang, $breadcrumb;
	if ($errormsg == NULL) {
		$errormsg = $lang->phrase('not_allowed');
	}
	if ($errorurl == NULL) {
		$errorurl = htmlspecialchars($_SERVER['REQUEST_URI']);
	}
	if (!is_array($errormsg)) {
		$errormsg = array($errormsg);
	}
	if (!isset($my->p)) {
		$my->p = $slog->Permissions();
	}
	$breadcrumb->Add($lang->phrase('breadcrumb_errorok'));
	if (!$tpl->tplsent('header') && !$tpl->tplsent('popup/header')) {
		echo $tpl->parse('header');
	}

	$tpl->globalvars(compact("errormsg","errorurl"));
    echo $tpl->parse("main/not_allowed");

	$slog->updatelogged();
	$zeitmessung = t2();
	if ($EOS != NULL) {
		echo $tpl->parse($EOS);
	}
	elseif ($tpl->tplsent('popup/header')) {
		echo $tpl->parse('popup/footer');
	}
	else {
		echo $tpl->parse('footer');
	}
	$phpdoc->Out();
	$db->close();
	exit;
}

function error ($errormsg=NULL,$errorurl='javascript:history.back(-1);', $EOS = NULL) {
	global $config, $my, $tpl, $zeitmessung, $db, $slog, $phpdoc, $lang, $breadcrumb;
	if ($errormsg == NULL) {
		$errormsg = $lang->phrase('unknown_error');
	}
	if (!is_array($errormsg)) {
		$errormsg = array($errormsg);
	}
	if (!isset($my->p)) {
		$my->p = $slog->Permissions();
	}
	$breadcrumb->Add($lang->phrase('breadcrumb_errorok'));
	if (!$tpl->tplsent('header') && !$tpl->tplsent('popup/header')) {
		echo $tpl->parse('header');
	}

	$tpl->globalvars(compact("errormsg","errorurl"));
    echo $tpl->parse("main/error");

	$slog->updatelogged();
	$zeitmessung = t2();
	if ($EOS != NULL) {
		echo $tpl->parse($EOS);
	}
	elseif ($tpl->tplsent('popup/header')) {
		echo $tpl->parse('popup/footer');
	}
	else {
		echo $tpl->parse('footer');
	}
	$phpdoc->Out();
	$db->close();
	exit;
}

function ok ($errormsg = NULL, $errorurl = "javascript: history.back(-1)", $EOS = NULL) {
	global $config, $my, $tpl, $zeitmessung, $db, $slog, $phpdoc, $lang, $breadcrumb;
	if ($errormsg == NULL) {
		$errormsg = $lang->phrase('unknown_ok');
	}
	if (!isset($my->p)) {
		$my->p = $slog->Permissions();
	}
	$breadcrumb->Add($lang->phrase('breadcrumb_errorok'));
	if (!$tpl->tplsent('header') && !$tpl->tplsent('popup/header')) {
		echo $tpl->parse('header');
	}
	
	$tpl->globalvars(compact("errormsg","errorurl"));
    echo $tpl->parse("main/ok");

	$slog->updatelogged();
	$zeitmessung = t2();
	if ($EOS != NULL) {
		echo $tpl->parse($EOS);
	}
	elseif ($tpl->tplsent('popup/header')) {
		echo $tpl->parse('popup/footer');
	}
	else {
		echo $tpl->parse('footer');
	}
	$phpdoc->Out();
	$db->close();
	exit;
}

function forum_opt($opt, $optvalue, $bid) {
	global $my, $lang, $tpl;
	if ($opt == 'pw' && (!isset($my->pwfaccess[$bid]) || $my->pwfaccess[$bid] != $optvalue)) {
    	if (!$tpl->tplsent('header')) {
    		echo $tpl->parse('header');
    	}
    	if (!$tpl->tplsent('menu')) {
    		echo $tpl->parse('menu');
    	}
	    GoBoardPW($optvalue, $bid);
	}
	elseif ($opt == "re") {
		error($lang->phrase('forumopt_re'),$optvalue);
	}
	elseif ($my->p['postreplies'] == 0 || $my->p['forum'] == 0) {
		errorLogin();
	}

}

function import_error_data($fid) {
	$scache = new scache('temp/errordata/'.$fid, '');
	$data = $scache->importdata();
	return $data;
}
function save_error_data($fc) {
	global $gpc;
	$fid = md5(microtime());
	$scache = new scache('temp/errordata/'.$fid, '');
	foreach ($fc as $key => $row) {
		$fc[$key] = $gpc->unescape($row);
	}
	$scache->exportdata($fc);
	return $fid;
}

function count_filled($array) {
	$int = 0;	
	foreach ($array as $val) {
		if (!empty($val)) {
			$int++;
		}
	}
	return $int;
}

function get_pmdir ($dir) {
	global $lang;

	if ($dir == '1') {
		$dir_name = $lang->phrase('pm_dirs_inbox');
	}
	elseif ($dir == '2') {
		$dir_name = $lang->phrase('pm_dirs_outbox');
	}
	elseif ($dir == '3') {
		$dir_name = $lang->phrase('pm_dirs_archive');
	}
	else {
		$dir_name = FALSE;
	}
	return $dir_name;
}

?>
