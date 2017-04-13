CREATE TABLE `{:=DBPREFIX=:}pm` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `topic` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `pm_from` int(7) unsigned NOT NULL,
  `pm_to` int(7) unsigned NOT NULL,
  `comment` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` int(10) unsigned NOT NULL DEFAULT 0,
  `status` enum('0','1') NOT NULL DEFAULT '0',
  `dir` enum('1','2','3') NOT NULL DEFAULT '1',
  PRIMARY KEY  (`id`),
  KEY `pm_to` (`pm_to`)
) ENGINE=MyISAM AUTO_INCREMENT=1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ;