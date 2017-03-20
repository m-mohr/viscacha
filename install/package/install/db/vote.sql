CREATE TABLE `{:=DBPREFIX=:}vote` (
  `id` mediumint(7) unsigned NOT NULL auto_increment,
  `tid` int(10) unsigned NOT NULL,
  `answer` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ;