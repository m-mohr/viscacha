CREATE TABLE `{:=DBPREFIX=:}prefix` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `bid` smallint(5) unsigned NOT NULL NOT NULL DEFAULT '',
  `value` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `standard` enum('0','1') NOT NULL NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ;
