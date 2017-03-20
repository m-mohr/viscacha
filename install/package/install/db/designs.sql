CREATE TABLE `{:=DBPREFIX=:}designs` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `template` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `stylesheet` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `images` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `publicuse` enum('0','1') NOT NULL NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ;

INSERT INTO `{:=DBPREFIX=:}designs` (`id`, `template`, `stylesheet`, `images`, `name`, `publicuse`) VALUES (1, 1, 1, 1, 'Viscacha 0.8 Standard', '1');