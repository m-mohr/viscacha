CREATE TABLE `{:=DBPREFIX=:}menu` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `param` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `groups` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `position` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'left',
  `ordering` smallint(4) NOT NULL DEFAULT 0,
  `sub` int(10) NOT NULL DEFAULT 0,
  `module` int(10) NOT NULL DEFAULT 0,
  `active` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ;

INSERT INTO `{:=DBPREFIX=:}menu` (`id`, `name`, `link`, `param`, `groups`, `position`, `ordering`, `sub`, `module`, `active`) VALUES
(1, 'lang->navigation', '', '', '0', 'sidebar', 0, 0, 0, '1'),
(2, 'lang->n_portal', 'portal.php', '', '0', 'sidebar', 0, 1, 0, '1'),
(3, 'doc->1', 'docs.php?id=1', '', '0', 'sidebar', 1, 1, 0, '1'),
(4, 'Common navigation elements', '', '', '0', 'sidebar', 1, 0, 15, '1'),
(8, 'Viscacha', '', '', '0', 'sidebar', 2, 0, 0, '1'),
(9, 'doc->2', 'docs.php?id=2', '', '0', 'sidebar', 0, 8, 0, '1'),
(11, 'Viscacha.org', 'http://www.viscacha.org', '_blank', '0', 'sidebar', 1, 8, 0, '1');