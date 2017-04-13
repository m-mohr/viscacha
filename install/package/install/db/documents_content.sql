CREATE TABLE `{:=DBPREFIX=:}documents_content` (
  `did` int(10) unsigned NOT NULL,
  `lid` smallint(4) unsigned NOT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `content` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` enum('0','1') NOT NULL DEFAULT '0',
  UNIQUE KEY `id` (`did`,`lid`)
) ENGINE=MyISAM CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

INSERT INTO `{:=DBPREFIX=:}documents_content` (`did`, `lid`, `title`, `content`, `active`) VALUES
(1, 2, 'Imprint', '<html>\r\n<head>\r\n  <title>Imprint</title>\r\n</head>\r\n<body>\r\n  <div class="border">\r\n    <h3>Imprint</h3>\r\n    <div class="bbody"><p>\r\n      John Doe<br />\r\n      Sample Street 100<br />\r\n      12345 Sample City<br />\r\n      Sample Country\r\n    </p><p>\r\n      Telefon: +49 123 12345-0<br />\r\n      Fax: +49 123 12345-1<br />\r\n      Mobil: +49 170 123456879<br />\r\n      Email: <img alt="Email" src="images.php?action=textimage&amp;text=<?php echo base64_encode($config[''forenmail'']); ?>&enc=1" border="0" />\r\n    </p></div>\r\n  </div>\r\n</body>\r\n</html>', '1'),
(1, 1, 'Impressum', '<html>\r\n<head>\r\n  <title>Impressum</title>\r\n</head>\r\n<body>\r\n  <div class="border">\r\n    <h3>Impressum</h3>\r\n    <div class="bbody"><p>\r\n      Matthias Mustermann<br />\r\n      Musterstr. 100<br />\r\n      12345 Musterstadt<br />\r\n      Musterland\r\n    </p><p>\r\n      Telefon: +49 123 12345-0<br />\r\n      Fax: +49 123 12345-1<br />\r\n      Mobil: +49 170 123456879<br />\r\n      Email: <img alt="E-Mail" src="images.php?action=textimage&amp;text=<?php echo base64_encode($config[''forenmail'']); ?>&enc=1" border="0" />\r\n    </p></div>\r\n  </div>\r\n</body>\r\n</html>', '1');