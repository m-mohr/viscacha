CREATE TABLE `{:=DBPREFIX=:}topics` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `board` smallint(5) unsigned NOT NULL NOT NULL DEFAULT '',
  `topic` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `prefix` smallint(5) unsigned NOT NULL NOT NULL DEFAULT '',
  `posts` int(10) unsigned NOT NULL NOT NULL DEFAULT '',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `date` int(10) unsigned NOT NULL NOT NULL DEFAULT '',
  `status` enum('0','1','2') NOT NULL NOT NULL DEFAULT '',
  `last` int(10) unsigned NOT NULL NOT NULL DEFAULT '',
  `sticky` enum('0','1') NOT NULL NOT NULL DEFAULT '',
  `last_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `vquestion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `last` (`last`),
  KEY `name` (`name`),
  KEY `board` (`board`),
  FULLTEXT KEY `topic` (`topic`)
) ENGINE=MyISAM AUTO_INCREMENT=1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ;