CREATE TABLE `{:=DBPREFIX=:}newsletter` (
  `id` mediumint(9) unsigned NOT NULL auto_increment,
  `receiver` tinyint(1) unsigned NOT NULL default '0',
  `title` varchar(255) NOT NULL default '',
  `content` longtext NOT NULL,
  `time` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;