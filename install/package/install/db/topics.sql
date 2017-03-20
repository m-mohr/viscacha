CREATE TABLE `{:=DBPREFIX=:}topics` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `board` smallint(5) unsigned NOT NULL,
  `topic` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `prefix` smallint(5) unsigned NOT NULL DEFAULT 0,
  `posts` int(10) unsigned NOT NULL DEFAULT 0,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `date` int(10) unsigned NOT NULL DEFAULT 0,
  `status` enum('0','1','2') NOT NULL DEFAULT '0',
  `last` int(10) unsigned NOT NULL DEFAULT 0,
  `sticky` enum('0','1') NOT NULL DEFAULT '0',
  `last_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `vquestion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `last` (`last`),
  KEY `name` (`name`),
  KEY `board` (`board`),
  FULLTEXT KEY `topic` (`topic`)
) ENGINE=MyISAM AUTO_INCREMENT=1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ;