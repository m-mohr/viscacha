CREATE TABLE `{:=DBPREFIX=:}bbcode` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `search` tinytext NOT NULL,
  `replace` tinytext NOT NULL,
  `type` enum('censor','bb','word','replace') NOT NULL default 'word',
  `desc` tinytext NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM PACK_KEYS=1 AUTO_INCREMENT=1 ;