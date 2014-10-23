CREATE TABLE `{:=DBPREFIX=:}vote` (
  `id` mediumint(7) unsigned NOT NULL auto_increment,
  `tid` int(10) unsigned NOT NULL default '0',
  `answer` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;