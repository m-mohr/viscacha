CREATE TABLE `{:=DBPREFIX=:}categories` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `name` varchar(200) NOT NULL default '',
  `desctxt` text NOT NULL,
  `c_order` smallint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;