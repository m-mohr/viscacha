CREATE TABLE `{:=DBPREFIX=:}cat` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `name` varchar(200) NOT NULL default '',
  `desc` text NOT NULL,
  `bid` smallint(5) unsigned NOT NULL default '0',
  `topics` int(10) unsigned NOT NULL default '0',
  `replys` int(10) unsigned NOT NULL default '0',
  `cid` smallint(5) unsigned NOT NULL default '0',
  `c_order` smallint(4) NOT NULL default '0',
  `last_topic` int(10) unsigned NOT NULL default '0',
  `opt` enum('','re','pw') NOT NULL default '',
  `optvalue` varchar(255) NOT NULL default '',
  `prefix` enum('0','1') NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `bid` (`bid`),
  KEY `cid` (`cid`),
  KEY `last_topic` (`last_topic`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;
