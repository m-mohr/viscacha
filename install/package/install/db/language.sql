CREATE TABLE `{:=DBPREFIX=:}language` (
  `id` smallint(4) unsigned NOT NULL auto_increment,
  `language` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `detail` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `publicuse` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ;

INSERT INTO `{:=DBPREFIX=:}language` VALUES (1, 'Deutsch (Formell)', 'German (formal) language pack', '1'),
(2, 'English', 'English language pack', '1');
