CREATE TABLE `{:=DBPREFIX=:}designs` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `template` tinyint(3) unsigned NOT NULL default '1',
  `stylesheet` tinyint(3) unsigned NOT NULL default '1',
  `images` tinyint(3) unsigned NOT NULL default '1',
  `name` varchar(255) NOT NULL default '',
  `publicuse` enum('0','1') NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 ;

INSERT INTO `{:=DBPREFIX=:}designs` (`id`, `template`, `stylesheet`, `images`, `name`, `publicuse`) VALUES (1, 1, 1, 1, 'Viscacha 0.8 Standard', '1');