CREATE TABLE `{:=DBPREFIX=:}textparser` (
  `id` mediumint(6) unsigned NOT NULL auto_increment,
  `search` varchar(255) NOT NULL default '',
  `replace` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  PACK_KEYS=1 AUTO_INCREMENT=2 ;

INSERT INTO `{:=DBPREFIX=:}textparser` (`search`, `replace`) VALUES
('[teaser]', '');
