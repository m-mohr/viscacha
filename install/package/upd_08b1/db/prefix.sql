CREATE TABLE `{:=DBPREFIX=:}prefix` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `bid` smallint(5) unsigned NOT NULL default '0',
  `value` varchar(128) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;