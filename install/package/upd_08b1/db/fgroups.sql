CREATE TABLE `{:=DBPREFIX=:}fgroups` (
  `fid` smallint(5) unsigned NOT NULL auto_increment,
  `f_downloadfiles` enum('0','1','-1') NOT NULL default '-1',
  `f_forum` enum('0','1','-1') NOT NULL default '-1',
  `f_posttopics` enum('0','1','-1') NOT NULL default '-1',
  `f_postreplies` enum('0','1','-1') NOT NULL default '-1',
  `f_addvotes` enum('0','1','-1') NOT NULL default '-1',
  `f_attachments` enum('0','1','-1') NOT NULL default '-1',
  `f_edit` enum('0','1','-1') NOT NULL default '-1',
  `f_voting` enum('0','1','-1') NOT NULL default '-1',
  `gid` smallint(5) unsigned NOT NULL default '0',
  `bid` smallint(5) unsigned NOT NULL default '0',
  PRIMARY KEY  (`fid`),
  KEY `bid` (`bid`),
  KEY `gid` (`gid`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;