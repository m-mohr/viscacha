CREATE TABLE `{:=DBPREFIX=:}language` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `language` varchar(128) NOT NULL default '',
  `detail` text NOT NULL,
  `publicuse` enum('0','1') NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM PACK_KEYS=0 AUTO_INCREMENT=2 ;

INSERT INTO `{:=DBPREFIX=:}language` (`id`, `language`, `detail`, `publicuse`) VALUES (1, 'Deutsch (Formell)', 'German (formal) language pack for Viscacha 0.8 Beta 1', '1');