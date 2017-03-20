CREATE TABLE `{:=DBPREFIX=:}uploads` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `tid` int(10) unsigned NOT NULL NOT NULL DEFAULT '',
  `topic_id` int(10) unsigned NOT NULL NOT NULL DEFAULT '',
  `mid` mediumint(7) unsigned NOT NULL NOT NULL DEFAULT '',
  `file` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `source` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `hits` int(10) unsigned NOT NULL NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`),
  KEY `tid` (`tid`)
) ENGINE=MyISAM AUTO_INCREMENT=1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ;
