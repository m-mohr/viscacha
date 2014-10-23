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
(1, 'Verwandte Themen', 1, 1, '1', 'showtopic_end'),
(2, 'Wer-ist-Online-Box', 2, 1, '1', 'forum_end'),
(3, 'News-Boxen', 3, 1, '1', 'portal'),
(4, 'Newsfeed-Ticker', 4, 1, '0', 'navigation'),
(5, 'Persönliche Box', 5, 2, '1', 'navigation'),
(6, 'Neue-PN-Box', 6, 2, '1', 'forum_end'),
(7, 'Nachrichten-Box', 7, 1, '1', 'forum_start'),
(8, 'Nachrichten-Box', 7, 2, '1', 'portal'),
(9, 'MS Word Druckansicht', 8, 1, '1', 'print_start'),
(10, 'Letzte-Themen-Box', 9, 3, '1', 'forum_end'),
(11, 'Letzte-Themen-Box', 9, 3, '1', 'portal'),
(12, 'Letzte-Antworten-Box', 10, 1, '1', 'addreply_form_end'),
(13, 'Legende (Themen)', 11, 1, '1', 'showforum_end'),
(14, 'Legende (Themen)', 11, 1, '1', 'search_result_end'),
(15, 'Legende (Themen)', 11, 1, '1', 'search_active_end'),
(16, 'Legende (PM)', 12, 1, '1', 'pm_browse_end'),
(17, 'Legende (PM)', 12, 1, '1', 'pm_index_end'),
(18, 'Legende (Foren)', 13, 4, '1', 'forum_end'),
(19, 'Birthday-Reminder', 14, 3, '1', 'navigation'),
(21, 'Login-Box', 16, 0, '1', 'forum_end');