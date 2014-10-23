CREATE TABLE `{:=DBPREFIX=:}component` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `file` varchar(127) NOT NULL default '',
  `package` mediumint(7) UNSIGNED NOT NULL default '0',
  `active` enum('0','1') NOT NULL default '1',
  `required` enum('0','1') NOT NULL default '1',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;