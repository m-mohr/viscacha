CREATE TABLE `{:=DBPREFIX=:}textparser` (
  `id` mediumint(6) unsigned NOT NULL auto_increment,
  `search` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `replace` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ;

INSERT INTO `{:=DBPREFIX=:}textparser` (`search`, `replace`) VALUES
('[teaser]', '');
