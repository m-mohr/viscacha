CREATE TABLE `{:=DBPREFIX=:}spellcheck` (
  `word` varchar(64) NOT NULL default '',
  `language` varchar(5) NOT NULL default ''
) TYPE=MyISAM;