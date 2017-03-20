CREATE TABLE `{:=DBPREFIX=:}abos` (
  `id` bigint(12) unsigned NOT NULL auto_increment,
  `mid` mediumint(7) unsigned NOT NULL,
  `tid` int(10) unsigned NOT NULL,
  `type` enum('','d','w','f') NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`),
  KEY `mid` (`mid`)
) ENGINE=MyISAM AUTO_INCREMENT=1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ;