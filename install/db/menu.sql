CREATE TABLE `{:=DBPREFIX=:}menu` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `link` varchar(255) NOT NULL default '',
  `param` varchar(64) NOT NULL default '',
  `groups` varchar(100) NOT NULL default '0',
  `ordering` smallint(4) NOT NULL default '0',
  `sub` int(10) NOT NULL default '0',
  `module` enum('0','1') NOT NULL default '0',
  `active` enum('0','1') NOT NULL default '0',
  `position` varchar(255) NOT NULL default 'navigation',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM PACK_KEYS=0 AUTO_INCREMENT=18 ;

INSERT INTO `{:=DBPREFIX=:}menu` (`id`, `name`, `link`, `param`, `groups`, `ordering`, `sub`, `module`, `active`, `position`) VALUES (1, 'Portal', 'portal.php', '', '0', 0, 3, '0', '1', 'navigation'),
(2, 'Forum', 'forum.php', '', '0', 1, 3, '0', '1', 'navigation'),
(3, 'Hauptmenü', '', '', '0', 0, 0, '0', '1', 'navigation'),
(4, 'Persönliche Box', '1', '', '0', 2, 0, '1', '1', 'navigation'),
(5, 'Wir gratulieren...', '3', '', '1,2,3,5', 3, 0, '1', '1', 'navigation'),
(6, 'Ankündigung', '5', '', '0', 1, 0, '1', '1', 'portal'),
(7, 'Aktuelle Nachrichten aus dem Forum', '6', '', '0', 0, 0, '1', '1', 'portal'),
(8, 'Die letzten aktiven Themen', '7', '', '0', 2, 0, '1', '1', 'portal,forum_bottom'),
(9, 'Druckausgabe für Word', '8', '', '0', 0, 0, '1', '1', 'print_top'),
(10, 'Letzte Antworten', '9', '', '0', 0, 0, '1', '1', 'addreply_bottom'),
(11, 'Verwandte Themen', '10', '', '0', 0, 0, '1', '1', 'showtopic_bottom'),
(12, 'Wer ist Online', '11', '', '0', 0, 0, '1', '1', 'forum_bottom'),
(13, 'Login-Box', '12', '', '4', 1, 0, '1', '1', 'forum_bottom'),
(14, 'Neue PNs', '13', '', '1,2,3,4,8', 2, 0, '1', '1', 'forum_bottom'),
(15, 'Legende (Forenübersicht)', '15', '', '0', 5, 0, '1', '1', 'forum_bottom'),
(16, 'Legende (Themenübersichten)', '16', '', '0', 2, 0, '1', '1', 'showforum_bottom,search_result_bottom,search_active_bottom'),
(17, 'Legende (PM)', '17', '', '0', 1, 0, '1', '1', 'pm_browse_bottom,pm_index_bottom');

