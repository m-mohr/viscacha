CREATE TABLE `{:=DBPREFIX=:}textparser` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `search` tinytext NOT NULL,
  `replace` tinytext NOT NULL,
  `type` enum('censor','word','replace') NOT NULL default 'word',
  `desc` tinytext NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM  PACK_KEYS=1 AUTO_INCREMENT=2 ;

INSERT INTO `{:=DBPREFIX=:}textparser` (`search`, `replace`, `type`, `desc`) VALUES
('[teaser]', '', 'censor', '');
