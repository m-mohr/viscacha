CREATE TABLE `{:=DBPREFIX=:}packages` (
  `id` mediumint(7) unsigned NOT NULL auto_increment,
  `title` varchar(200) NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM PACK_KEYS=0 AUTO_INCREMENT=17 ;

INSERT INTO `{:=DBPREFIX=:}packages` (`id`, `title`) VALUES (1, 'Verwandte Themen'),
(2, 'Wer-ist-Online-Box'),
(3, 'News-Boxen'),
(4, 'Newsfeed-Ticker'),
(5, 'Persönliche Box'),
(6, 'Neue-PN-Box'),
(7, 'Nachrichten-Box'),
(8, 'MS Word Druckansicht'),
(9, 'Letzte-Themen-Box'),
(10, 'Letzte-Antworten-Box'),
(11, 'Legende (Themen)'),
(12, 'Legende (PM)'),
(13, 'Legende (Foren)'),
(14, 'Birthday-Reminder'),
(16, 'Login-Box');