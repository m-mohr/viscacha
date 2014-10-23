CREATE TABLE `{:=DBPREFIX=:}packages` (
  `id` mediumint(7) unsigned NOT NULL auto_increment,
  `title` varchar(200) NOT NULL default '',
  `active` enum('0','1') NOT NULL default '0',
  `version` varchar(64) NOT NULL default '',
  `internal` varchar(100) NOT NULL default '',
  `core` enum('0','1') NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM PACK_KEYS=0 AUTO_INCREMENT=10 ;

INSERT INTO `{:=DBPREFIX=:}packages` (`id`, `title`, `active`, `version`, `internal`, `core`) VALUES
(1, 'Related Topics', '1', '0.8', 'viscacha_related_topics', '0'),
(2, 'Who is online', '1', '0.8', 'viscacha_who_is_online', '0'),
(3, 'News Boxes', '1', '0.8.3', 'viscacha_news_boxes', '0'),
(4, 'Last replies', '1', '0.8.1', 'viscacha_addreply_last_replies', '0'),
(5, 'Legends', '1', '0.8.1', 'viscacha_help_legends', '0'),
(6, 'Personal Panels', '1', '0.8', 'viscacha_personal_panels', '0'),
(7, 'Quick Reply', '1', '0.8.1', 'viscacha_quick_reply', '0'),
(8, 'Recent Topics', '1', '0.8.2', 'viscacha_recent_topics', '0'),
(9, 'Document on Portal', '1', '1.0 Beta 1', 'viscacha_document_on_portal', '0');
