CREATE TABLE `{:=DBPREFIX=:}postratings` (
  `mid` mediumint(7) NOT NULL,
  `aid` mediumint(7) NOT NULL,
  `tid` int(10) NOT NULL,
  `pid` int(10) NOT NULL,
  `rating` tinyint(1) NOT NULL default '0',
  UNIQUE KEY `mid` (`mid`,`pid`)
) TYPE=MyISAM;
