CREATE TABLE `{:=DBPREFIX=:}documents_content` (
  `did` int(10) unsigned NOT NULL,
  `lid` smallint(4) unsigned NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `content` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` enum('0','1') NOT NULL DEFAULT '0',
  UNIQUE KEY `id` (`did`,`lid`)
) ENGINE=MyISAM CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

INSERT INTO `{:=DBPREFIX=:}documents_content` (`did`, `lid`, `title`, `content`, `active`) VALUES
(1, 2, 'Imprint', '<p>\r\nJohn Doe<br />\r\nSample Street 100<br />\r\n12345 Sample City<br />\r\nSample Country\r\n</p><p>\r\nTelefon: +49 123 12345-0<br />\r\nFax: +49 123 12345-1<br />\r\nMobil: +49 170 123456879<br />\r\nEmail: webmaster@example.com\r\n</p>', '1'),
(1, 1, 'Impressum', '<p>\r\nMatthias Mustermann<br />\r\nMusterstr. 100<br />\r\n12345 Musterstadt<br />\r\nMusterland\r\n</p><p>\r\nTelefon: +49 123 12345-0<br />\r\nFax: +49 123 12345-1<br />\r\nMobil: +49 170 123456879<br />\r\nEmail: webmaster@example.com\r\n</p>', '1');