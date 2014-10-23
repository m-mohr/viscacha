CREATE TABLE `{:=DBPREFIX=:}documents` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `content` longtext NOT NULL,
  `author` mediumint(7) unsigned NOT NULL default '0',
  `date` int(10) unsigned NOT NULL default '0',
  `update` int(10) unsigned NOT NULL default '0',
  `type` tinyint(2) NOT NULL default '0',
  `groups` varchar(150) NOT NULL default '',
  `active` enum('0','1') NOT NULL default '0',
  `file` tinytext NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM AUTO_INCREMENT=3 ;

INSERT INTO `{:=DBPREFIX=:}documents` (`id`, `title`, `content`, `author`, `date`, `update`, `type`, `groups`, `active`, `file`) VALUES (1, 'Impressum', '', 3, 1139678039, 1139678039, 5, '0', '1', 'docs/impressum.php'),
(2, 'Credits', '', 3, 1139678141, 1139678141, 5, '0', '1', 'docs/credits.php');
