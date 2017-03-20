CREATE TABLE `{:=DBPREFIX=:}categories` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent` smallint(5) unsigned NOT NULL NOT NULL DEFAULT '',
  `position` smallint(4) NOT NULL NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ;