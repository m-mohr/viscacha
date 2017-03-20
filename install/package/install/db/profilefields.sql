CREATE TABLE `{:=DBPREFIX=:}profilefields` (
  `fid` smallint(5) unsigned NOT NULL auto_increment,
  `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `disporder` smallint(5) unsigned NOT NULL NOT NULL DEFAULT '',
  `type` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `length` smallint(5) unsigned NOT NULL NOT NULL DEFAULT '',
  `maxlength` smallint(5) unsigned NOT NULL NOT NULL DEFAULT '',
  `required` enum('0','1') NOT NULL NOT NULL DEFAULT '',
  `editable` enum('0','1','2') NOT NULL DEFAULT '1',
  `viewable` enum('0','1','2','3') NOT NULL DEFAULT '1',
  PRIMARY KEY  (`fid`)
) ENGINE=MyISAM AUTO_INCREMENT=1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ;