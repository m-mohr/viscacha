CREATE TABLE `{:=DBPREFIX=:}uploads` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tid` int(10) unsigned NOT NULL default '0',
  `topic_id` int(10) unsigned NOT NULL default '0',
  `mid` mediumint(7) unsigned NOT NULL default '0',
  `file` varchar(128) NOT NULL default '',
  `source` varchar(128) NOT NULL default '',
  `hits` int(10) unsigned NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `tid` (`tid`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;
