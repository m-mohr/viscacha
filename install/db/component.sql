CREATE TABLE `{:=DBPREFIX=:}component` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `file` varchar(255) NOT NULL default '',
  `active` enum('0','1') NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=0 ;