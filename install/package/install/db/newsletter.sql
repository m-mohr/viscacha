CREATE TABLE `{:=DBPREFIX=:}newsletter` (
  `id` mediumint(9) unsigned NOT NULL auto_increment,
  `receiver` longtext NOT NULL,
  `title` varchar(255) NOT NULL default '',
  `sender` varchar(255) NOT NULL default '',
  `content` longtext NOT NULL,
  `time` int(10) unsigned NOT NULL default '0',
  `type` enum('p', 'h') default 'p',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;