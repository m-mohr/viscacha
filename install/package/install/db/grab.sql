CREATE TABLE `{:=DBPREFIX=:}grab` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `file` varchar(255) NOT NULL default '',
  `title` varchar(255) NOT NULL default '',
  `entries` smallint(2) unsigned NOT NULL default '15',
  `max_age` mediumint(6) unsigned NOT NULL default '3600',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;