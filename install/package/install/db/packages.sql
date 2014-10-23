CREATE TABLE `{:=DBPREFIX=:}packages` (
  `id` mediumint(7) unsigned NOT NULL auto_increment,
  `title` varchar(200) NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM PACK_KEYS=0 AUTO_INCREMENT=17 ;

INSERT INTO `{:=DBPREFIX=:}packages` (`id`, `title`) VALUES (1, 'Related Topics'),
(2, 'Who-is-Online Box'),
(3, 'News Boxes'),
(4, 'Newsfeed-Ticker'),
(5, 'Personal Box'),
(6, 'New PM Box'),
(7, 'Message Box'),
(8, 'Export to MS Word'),
(9, 'Last topic Box'),
(10, 'Last reply Box'),
(11, 'Legend (Topics)'),
(12, 'Legend (PM)'),
(13, 'Legend (Forums)'),
(14, 'Birthday-Reminder'),
(16, 'Login Box');
