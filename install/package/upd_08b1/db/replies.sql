CREATE TABLE `{:=DBPREFIX=:}replies` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `board` smallint(5) unsigned NOT NULL default '0',
  `topic` varchar(255) NOT NULL default '',
  `topic_id` int(10) unsigned NOT NULL default '0',
  `name` varchar(50) NOT NULL default '',
  `guest` enum('0','1') NOT NULL default '0',
  `comment` longtext NOT NULL,
  `dosmileys` enum('0','1') NOT NULL default '1',
  `dowords` enum('0','1') NOT NULL default '1',
  `email` varchar(200) NOT NULL default '',
  `ip` varchar(20) NOT NULL,
  `date` int(10) unsigned NOT NULL default '0',
  `edit` text NOT NULL,
  `tstart` enum('0','1') NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `topic_id` (`topic_id`),
  KEY `date` (`date`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;