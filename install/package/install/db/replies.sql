CREATE TABLE `{:=DBPREFIX=:}replies` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `topic` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `topic_id` int(10) unsigned NOT NULL,
  `name` int(10) unsigned NOT NULL,
  `comment` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `dosmileys` enum('0','1') NOT NULL DEFAULT '1',
  `ip` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `date` int(10) unsigned NOT NULL DEFAULT 0,
  `edit` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `report` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tstart` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `topic_id` (`topic_id`),
  KEY `date` (`date`)
) ENGINE=MyISAM AUTO_INCREMENT=1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ;