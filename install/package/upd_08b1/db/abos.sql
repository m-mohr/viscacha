CREATE TABLE `{:=DBPREFIX=:}abos` (
  `id` bigint(12) unsigned NOT NULL auto_increment,
  `mid` mediumint(7) unsigned NOT NULL default '0',
  `tid` int(10) unsigned NOT NULL default '0',
  `type` enum('','d','w','f') NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `mid` (`mid`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;