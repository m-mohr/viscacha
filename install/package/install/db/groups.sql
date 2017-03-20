CREATE TABLE `{:=DBPREFIX=:}groups` (
  `id` smallint(5) unsigned NOT NULL auto_increment,
  `admin` enum('0','1') NOT NULL NOT NULL DEFAULT '',
  `gmod` enum('0','1') NOT NULL NOT NULL DEFAULT '',
  `guest` enum('0','1') NOT NULL NOT NULL DEFAULT '',
  `members` enum('0','1') NOT NULL NOT NULL DEFAULT '',
  `profile` enum('0','1') NOT NULL NOT NULL DEFAULT '',
  `pm` enum('0','1') NOT NULL NOT NULL DEFAULT '',
  `wwo` enum('0','1') NOT NULL NOT NULL DEFAULT '',
  `search` enum('0','1') NOT NULL NOT NULL DEFAULT '',
  `team` enum('0','1') NOT NULL NOT NULL DEFAULT '',
  `usepic` enum('0','1') NOT NULL NOT NULL DEFAULT '',
  `useabout` enum('0','1') NOT NULL NOT NULL DEFAULT '',
  `usesignature` enum('0','1') NOT NULL NOT NULL DEFAULT '',
  `downloadfiles` enum('0','1') NOT NULL NOT NULL DEFAULT '',
  `forum` enum('0','1') NOT NULL NOT NULL DEFAULT '',
  `posttopics` enum('0','1') NOT NULL NOT NULL DEFAULT '',
  `postreplies` enum('0','1') NOT NULL NOT NULL DEFAULT '',
  `addvotes` enum('0','1') NOT NULL NOT NULL DEFAULT '',
  `attachments` enum('0','1') NOT NULL NOT NULL DEFAULT '',
  `edit` enum('0','1') NOT NULL NOT NULL DEFAULT '',
  `voting` enum('0','1') NOT NULL NOT NULL DEFAULT '',
  `flood` smallint(5) unsigned NOT NULL DEFAULT '15',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `core` enum('0','1') NOT NULL NOT NULL DEFAULT '',
  PRIMARY KEY  (`id`),
  KEY `core` (`core`)
) ENGINE=MyISAM AUTO_INCREMENT=1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ;

INSERT INTO `{:=DBPREFIX=:}groups` (`id`, `admin`, `gmod`, `guest`, `members`, `profile`, `pm`, `wwo`, `search`, `team`, `usepic`, `useabout`, `usesignature`, `downloadfiles`, `forum`, `posttopics`, `postreplies`, `addvotes`, `attachments`, `edit`, `voting`, `flood`, `title`, `name`, `core`) VALUES
(1, '1', '1', '0', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', 1, 'Administrator', 'Administratoren', '1'),
(2, '0', '1', '0', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', 15, 'G-Mod', 'Super Moderatoren', '0'),
(4, '0', '0', '0', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', 20, 'Mitglied', 'Mitglieder', '1'),
(5, '0', '0', '1', '0', '0', '0', '0', '1', '1', '0', '0', '0', '1', '1', '1', '1', '0', '0', '0', '0', 60, 'Gast', 'Gäste', '1'),
(3, '0', '0', '0', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', '1', 15, 'Moderator', 'Moderatoren', '0');