CREATE TABLE `{:=DBPREFIX=:}documents` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `author` int(7) unsigned NOT NULL DEFAULT 0,
  `date` int(10) unsigned NOT NULL DEFAULT 0,
  `update` int(10) unsigned NOT NULL DEFAULT 0,
  `type` tinyint(2) NOT NULL DEFAULT 0,
  `groups` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `icomment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  AUTO_INCREMENT=1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ;

INSERT INTO `{:=DBPREFIX=:}documents` (`id`, `author`, `date`, `update`, `type`, `groups`, `icomment`) VALUES
(1, 1, 1205883294, 1205883294, 5, '0', 'Please update your imprint after setup!');