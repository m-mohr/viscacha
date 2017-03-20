CREATE TABLE `{:=DBPREFIX=:}moderators` (
  `mid` mediumint(7) NOT NULL,
  `bid` smallint(5) NOT NULL,
  `p_delete` enum('0','1') NOT NULL DEFAULT '1',
  `p_mc` enum('0','1') NOT NULL DEFAULT '1',
  `time` int(10) unsigned NOT NULL DEFAULT 0,
  UNIQUE KEY `mid` (`mid`,`bid`)
) ENGINE=MyISAM CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ;