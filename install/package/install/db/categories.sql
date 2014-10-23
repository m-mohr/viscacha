CREATE TABLE `{:=DBPREFIX=:}categories` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `name` varchar(200) NOT NULL default '',
  `description` text NOT NULL,
  `parent` smallint(5) unsigned NOT NULL default '0',
  `position` smallint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;