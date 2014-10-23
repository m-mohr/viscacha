CREATE TABLE `{:=DBPREFIX=:}language` (
  `id` smallint(4) unsigned NOT NULL auto_increment,
  `language` varchar(128) NOT NULL default '',
  `detail` text NOT NULL,
  `publicuse` enum('0','1') NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM PACK_KEYS=0 AUTO_INCREMENT=4 ;

INSERT INTO `{:=DBPREFIX=:}language` VALUES (1, 'Deutsch (Formell)', 'German (formal) language pack', '1'),
(2, 'English', 'English language pack', '1');
