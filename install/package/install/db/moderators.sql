CREATE TABLE `{:=DBPREFIX=:}moderators` (
  `mid` mediumint(7) NOT NULL default '0',
  `bid` smallint(5) NOT NULL default '0',
  `p_delete` enum('0','1') NOT NULL default '1',
  `p_mc` enum('0','1') NOT NULL default '1',
  UNIQUE KEY `mid` (`mid`,`bid`)
) ENGINE=MyISAM;