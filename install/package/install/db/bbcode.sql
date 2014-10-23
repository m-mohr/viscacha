CREATE TABLE `{:=DBPREFIX=:}bbcode` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `bbcodetag` varchar(200) NOT NULL default '',
  `bbcodereplacement` mediumtext NOT NULL,
  `bbcodeexample` varchar(255) NOT NULL,
  `bbcodeexplanation` mediumtext NOT NULL,
  `twoparams` enum('0','1') NOT NULL default '0',
  `title` varchar(200) NOT NULL,
  `buttonimage` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `uniquetag` (`bbcodetag`,`twoparams`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;