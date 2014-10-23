CREATE TABLE `{:=DBPREFIX=:}plugins` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(200) NOT NULL,
  `module` mediumint(7) unsigned NOT NULL,
  `ordering` smallint(4) NOT NULL default '0',
  `active` enum('0','1') NOT NULL default '0',
  `position` varchar(128) NOT NULL default 'navigation',
  PRIMARY KEY  (`id`),
  KEY `position` (`position`)
) TYPE=MyISAM PACK_KEYS=0 AUTO_INCREMENT=22 ;

INSERT INTO `{:=DBPREFIX=:}plugins` (`id`, `name`, `module`, `ordering`, `active`, `position`) VALUES 
(1, 'Related Topics', 1, 1, '1', 'showtopic_end'),
(2, 'Who-is-Online Box', 2, 1, '1', 'forum_end'),
(3, 'News Boxes', 3, 1, '1', 'portal'),
(4, 'Newsfeed-Ticker', 4, 1, '0', 'navigation'),
(5, 'Personal Box', 5, 2, '1', 'navigation'),
(6, 'New PM Box', 6, 2, '1', 'forum_end'),
(7, 'Message Box', 7, 1, '1', 'forum_start'),
(8, 'Message Box', 7, 2, '1', 'portal'),
(9, 'Export to MS Word', 8, 1, '1', 'print_start'),
(10, 'Last topic Box', 9, 3, '1', 'forum_end'),
(11, 'Last topic Box', 9, 3, '1', 'portal'),
(12, 'Last reply Box', 10, 1, '1', 'addreply_form_end'),
(13, 'Legend (Topics)', 11, 1, '1', 'showforum_end'),
(14, 'Legend (Topics)', 11, 1, '1', 'search_result_end'),
(15, 'Legend (Topics)', 11, 1, '1', 'search_active_end'),
(16, 'Legend (PM)', 12, 1, '1', 'pm_browse_end'),
(17, 'Legend (PM)', 12, 1, '1', 'pm_index_end'),
(18, 'Legend (Forums)', 13, 4, '1', 'forum_end'),
(19, 'Birthday-Reminder', 14, 3, '1', 'navigation'),
(21, 'Login Box', 16, 0, '1', 'forum_end');
