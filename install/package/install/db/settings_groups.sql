CREATE TABLE `{:=DBPREFIX=:}settings_groups` (
  `id` smallint(4) unsigned NOT NULL auto_increment,
  `title` varchar(120) NOT NULL,
  `name` varchar(120) NOT NULL,
  `description` tinytext NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM PACK_KEYS=0 AUTO_INCREMENT=7 ;

INSERT INTO `{:=DBPREFIX=:}settings_groups` (`id`, `title`, `name`, `description`) VALUES 
(1, 'Related Topics', 'module_1', 'Configuration for plugin 1'),
(2, 'News Boxes', 'module_3', 'Configuration for plugin 3'),
(3, 'Newsfeed-Ticker', 'module_4', 'Configuration for plugin 4'),
(4, 'Message Box', 'module_7', 'Configuration for plugin 7'),
(5, 'Last topic Box', 'module_9', 'Configuration for plugin 9'),
(6, 'Last reply Box', 'module_10', 'Configuration for plugin 10');
