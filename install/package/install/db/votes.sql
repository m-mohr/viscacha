CREATE TABLE `{:=DBPREFIX=:}votes` (
  `id` bigint(19) unsigned NOT NULL auto_increment,
  `mid` mediumint(7) unsigned NOT NULL default '0',
  `aid` mediumint(7) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `aid` (`aid`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;