CREATE TABLE `{:=DBPREFIX=:}flood` (
  `ip` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `mid` mediumint(7) unsigned NOT NULL NOT NULL DEFAULT '',
  `time` int(10) unsigned NOT NULL,
  `type` varchar(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sta'
) ENGINE=MyISAM CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ;