CREATE TABLE `{:=DBPREFIX=:}profilefields` (
  `fid` smallint(5) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `description` text NOT NULL,
  `disporder` smallint(5) unsigned NOT NULL default '0',
  `type` text NOT NULL,
  `length` smallint(5) unsigned NOT NULL default '0',
  `maxlength` smallint(5) unsigned NOT NULL default '0',
  `required` enum('0','1') NOT NULL default '0',
  `editable` enum('0','1','2') NOT NULL default '1',
  `viewable` enum('0','1','2','3') NOT NULL default '1',
  PRIMARY KEY  (`fid`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;