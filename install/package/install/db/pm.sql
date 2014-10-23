CREATE TABLE `{:=DBPREFIX=:}pm` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `topic` varchar(255) NOT NULL default '',
  `pm_from` mediumint(7) unsigned NOT NULL default '0',
  `pm_to` mediumint(7) unsigned NOT NULL default '0',
  `comment` mediumtext NOT NULL,
  `date` int(10) unsigned NOT NULL default '0',
  `status` enum('0','1') NOT NULL default '0',
  `dir` enum('1','2','3') NOT NULL default '1',
  PRIMARY KEY  (`id`),
  KEY `pm_to` (`pm_to`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;