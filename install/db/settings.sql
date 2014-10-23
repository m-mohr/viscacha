DROP TABLE IF EXISTS `{:=DBPREFIX=:}settings`;
CREATE TABLE `{:=DBPREFIX=:}settings` (
  `name` varchar(120) NOT NULL default '',
  `title` varchar(150) NOT NULL default '',
  `description` text NOT NULL,
  `type` enum('text','textarea','select','checkbox') NOT NULL default 'text',
  `optionscode` text NOT NULL,
  `value` text NOT NULL,
  UNIQUE KEY `name` (`name`)
) TYPE=MyISAM;