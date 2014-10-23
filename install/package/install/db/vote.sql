CREATE TABLE `{:=DBPREFIX=:}vote` (
  `id` mediumint(7) unsigned NOT NULL auto_increment,
  `tid` int(10) unsigned NOT NULL default '0',
  `answer` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;