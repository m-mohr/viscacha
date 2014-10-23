CREATE TABLE `{:=DBPREFIX=:}fav` (
  `id` bigint(12) unsigned NOT NULL auto_increment,
  `mid` mediumint(7) unsigned NOT NULL default '0',
  `tid` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `tid` (`tid`),
  KEY `mid` (`mid`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;