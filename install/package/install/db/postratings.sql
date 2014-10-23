CREATE TABLE `{:=DBPREFIX=:}postratings` (
  `mid` mediumint(7) NOT NULL default '0',
  `aid` mediumint(7) NOT NULL default '0',
  `tid` int(10) NOT NULL default '0',
  `pid` int(10) NOT NULL default '0',
  `rating` tinyint(1) NOT NULL default '0',
  UNIQUE KEY `mid` (`mid`,`pid`)
) ENGINE=MyISAM;
