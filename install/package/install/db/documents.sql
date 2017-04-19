CREATE TABLE `{:=DBPREFIX=:}documents` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `author` int(7) unsigned NOT NULL DEFAULT 0,
  `date` int(10) unsigned NOT NULL DEFAULT 0,
  `update` int(10) unsigned NOT NULL DEFAULT 0,
  `parser` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `template` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `groups` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `icomment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ;

INSERT INTO `v_documents` (`id`, `author`, `date`, `update`, `parser`, `template`, `groups`, `icomment`) VALUES
(1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 'html', 'default', '0', 'Please update your imprint after setup!');