<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }
$config = array();
$config['abozahl'] = 20;
$config['acceptrules'] = 1;
$config['activezahl'] = 20;
$config['avfilesize'] = 10240;
$config['avfiletypes'] = 'gif,png,jpg';
$config['avheight'] = 100;
$config['avwidth'] = 100;
$config['botgfxtest'] = 0;
$config['botgfxtest_posts'] = 0;
$config['botgfxtest_recaptcha_private'] = '';
$config['botgfxtest_recaptcha_public'] = '';
$config['censorstatus'] = 2;
$config['changename_allowed'] = 0;
$config['confirm_registration'] = '11';
$config['cookie_prefix'] = 'vc';
$config['correctsubdomains'] = 0;
$config['cryptkey'] = '';
$config['database'] = '';
$config['dbprefix'] = 'v_';
$config['dbpw'] = '';
$config['dbsystem'] = 'mysqli';
$config['dbuser'] = '';
$config['debug'] = 0;
$config['decimals'] = 2;
$config['disableregistration'] = 0;
$config['doclang'] = 2;
$config['edit_delete_time'] = 15;
$config['edit_edit_time'] = 0;
$config['email_check_mx'] = 0;
$config['enableflood'] = 1;
$config['error_log'] = 0;
$config['fdesc'] = '';
$config['floodsearch'] = 1;
$config['fname'] = '';
$config['foffline'] = 0;
$config['foffline_message'] = '<p>Sorry, the board is unavailable at the moment while we are doing some maintenance work.</p>'."\r\n".'<p>We will be back soon...</p>';
$config['forenmail'] = '';
$config['forumzahl'] = 15;
$config['fpath'] = '';
$config['ftp_path'] = '';
$config['ftp_port'] = 21;
$config['ftp_pw'] = '';
$config['ftp_server'] = '';
$config['ftp_user'] = '';
$config['fullname_posts'] = 1;
$config['furl'] = '';
$config['guest_email_optional'] = 0;
$config['hidedesign'] = 0;
$config['hidelanguage'] = 0;
$config['host'] = 'localhost';
$config['hterrordocs'] = 0;
$config['indexpage'] = 'portal';
$config['langdir'] = 1;
$config['lasttopic_chars'] = 40;
$config['local_mode'] = 0;
$config['login_attempts_blocktime'] = 60;
$config['login_attempts_max'] = 5;
$config['login_attempts_time'] = 60;
$config['maxaboutlength'] = 10000;
$config['maxeditlength'] = 128;
$config['maxmultiquote'] = 10;
$config['maxnamelength'] = 50;
$config['maxpostlength'] = 10000;
$config['maxpwlength'] = 32;
$config['maxsearchresults'] = 250;
$config['maxsiglength'] = 200;
$config['maxtitlelength'] = 100;
$config['maxurllength'] = 50;
$config['maxurltrenner'] = '...';
$config['maxwordlength'] = 50;
$config['maxwordlengthchar'] = '<br />';
$config['mineditlength'] = 0;
$config['minnamelength'] = 3;
$config['minpostlength'] = 10;
$config['minpwlength'] = 4;
$config['mintitlelength'] = 6;
$config['mlistenzahl'] = 15;
$config['mlist_fields'] = 'fullname,regdate,hp,online';
$config['mlist_filtergroups'] = 0;
$config['mlist_showinactive'] = 0;
$config['multiple_instant_notifications'] = 0;
$config['mylastzahl'] = 10;
$config['new_dformat4'] = 1;
$config['nocache'] = 1;
$config['optimizetables'] = 'session,abos,replies,topics,pm,uploads,user,vote,votes,flood';
$config['pccron'] = 1;
$config['pccron_maxjobs'] = 3;
$config['pccron_sendlog'] = 0;
$config['pccron_sendlog_email'] = '';
$config['pccron_uselog'] = 0;
$config['pmzahl'] = 15;
$config['post_order'] = 0;
$config['reduce_endchars'] = 1;
$config['reduce_nl'] = 1;
$config['reduce_url'] = 1;
$config['register_notification'] = '';
$config['resizebigimg'] = 1;
$config['resizebigimgwidth'] = 550;
$config['searchminlength'] = 3;
$config['searchzahl'] = 10;
$config['sendmail'] = 0;
$config['sendmail_host'] = '';
$config['sessionrefresh'] = 180;
$config['sessionsave'] = 15;
$config['session_checkip'] = 3;
$config['showpostcounter'] = 1;
$config['showsubfs'] = 1;
$config['sig_bbcode'] = 0;
$config['sig_bbedit'] = 0;
$config['sig_bbh'] = 0;
$config['sig_bbimg'] = 1;
$config['sig_bblist'] = 1;
$config['sig_bbot'] = 0;
$config['smileypath'] = 'images/smileys';
$config['smileyurl'] = 'images/smileys';
$config['smtp'] = 0;
$config['smtp_auth'] = 0;
$config['smtp_host'] = '';
$config['smtp_password'] = '';
$config['smtp_username'] = '';
$config['templatedir'] = 1;
$config['timezone'] = '+1';
$config['topicuppercase'] = 1;
$config['topiczahl'] = 10;
$config['tpcallow'] = 1;
$config['tpcfilesize'] = 512000;
$config['tpcfiletypes'] = 'gif,jpeg,jpg,jpe,png,doc,txt,rtf,zip,rar,tar,gz,pdf,htm,html,css,js,bmp';
$config['tpcheight'] = 2048;
$config['tpcmaxuploads'] = 3;
$config['tpcthumbheight'] = 150;
$config['tpcthumbwidth'] = 200;
$config['tpcwidth'] = 2048;
$config['updateboardstats'] = 1;
$config['updatepostcounter'] = 1;
$config['version'] = '0.9 Alpha 1';
$config['viscacha_addreply_last_replies']['repliesnum'] = '5';
$config['viscacha_document_on_portal']['doc_id'] = '0';
$config['viscacha_news_boxes']['board'] = '0';
$config['viscacha_news_boxes']['cutat'] = 'teaser';
$config['viscacha_news_boxes']['items'] = '5';
$config['viscacha_news_boxes']['teaserlength'] = '300';
$config['viscacha_recent_topics']['topicnum'] = '10';
$config['viscacha_related_topics']['hide_empty'] = '1';
$config['viscacha_related_topics']['relatednum'] = '5';
$config['vote_change'] = 0;
$config['wordwrap'] = 1;
?>