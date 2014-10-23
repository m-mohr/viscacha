CREATE TABLE `{:=DBPREFIX=:}designs` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `template` tinyint(3) unsigned NOT NULL default '1',
  `stylesheet` tinyint(3) unsigned NOT NULL default '1',
  `images` tinyint(3) unsigned NOT NULL default '1',
  `smileyfolder` varchar(255) NOT NULL default '',
  `smileypath` varchar(255) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `publicuse` enum('0','1') NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=2 ;

INSERT INTO `{:=DBPREFIX=:}designs` (`id`, `template`, `stylesheet`, `images`, `smileyfolder`, `smileypath`, `name`, `publicuse`) VALUES (1, 1, 1, 1, '{folder}/smilies/1', '{folder}/smilies/1', 'Viscacha 0.8 Standard', '1');