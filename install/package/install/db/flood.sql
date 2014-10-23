CREATE TABLE `{:=DBPREFIX=:}flood` (
  `ip` varchar(16) NOT NULL default '',
  `mid` mediumint(7) unsigned NOT NULL default '0',
  `time` int(10) unsigned NOT NULL,
  `type` varchar(3) NOT NULL default 'sta'
) ENGINE=MyISAM;