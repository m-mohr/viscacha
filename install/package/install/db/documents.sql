CREATE TABLE `{:=DBPREFIX=:}documents` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `author` mediumint(7) unsigned NOT NULL default '0',
  `date` int(10) unsigned NOT NULL default '0',
  `update` int(10) unsigned NOT NULL default '0',
  `type` tinyint(2) NOT NULL default '0',
  `groups` varchar(150) NOT NULL default '',
  `icomment` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM  AUTO_INCREMENT=2 ;

INSERT INTO `{:=DBPREFIX=:}documents` (`id`, `author`, `date`, `update`, `type`, `groups`, `icomment`) VALUES
(1, 1, 1205883294, 1205883294, 5, '0', 'Please update your imprint after setup!');