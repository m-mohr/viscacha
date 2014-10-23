CREATE TABLE `{:=DBPREFIX=:}textparser` (
  `id` mediumint(6) unsigned NOT NULL auto_increment,
  `search` varchar(255) NOT NULL default '',
  `replace` varchar(255) NOT NULL default '',
  `type` enum('censor','word','replace') NOT NULL default 'word',
  `desc` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM  PACK_KEYS=1 AUTO_INCREMENT=2 ;

INSERT INTO `{:=DBPREFIX=:}textparser` (`search`, `replace`, `type`, `desc`) VALUES
('[teaser]', '', 'censor', '');
