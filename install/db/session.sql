CREATE TABLE `{:=DBPREFIX=:}session` (
  `mid` mediumint(7) unsigned NOT NULL default '0',
  `active` int(10) unsigned NOT NULL default '0',
  `wiw_script` varchar(50) NOT NULL default '',
  `wiw_action` varchar(50) NOT NULL default '',
  `wiw_id` int(10) unsigned default NULL,
  `ip` varchar(16) NOT NULL default '',
  `remoteaddr` text NOT NULL,
  `lastvisit` int(10) unsigned NOT NULL default '0',
  `mark` longtext NOT NULL,
  `sid` varchar(128) NOT NULL default '',
  `pwfaccess` tinytext NOT NULL,
  `settings` tinytext NOT NULL,
  KEY `mid` (`mid`)
) TYPE=MyISAM;