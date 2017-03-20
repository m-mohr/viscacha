CREATE TABLE `{:=DBPREFIX=:}votes` (
  `id` bigint(19) unsigned NOT NULL auto_increment,
  `mid` mediumint(7) unsigned NOT NULL NOT NULL DEFAULT '',
  `aid` mediumint(7) unsigned NOT NULL NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`),
  KEY `aid` (`aid`)
) ENGINE=MyISAM AUTO_INCREMENT=1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ;