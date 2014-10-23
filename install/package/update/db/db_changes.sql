ALTER TABLE `{:=DBPREFIX=:}component` MODIFY COLUMN `package` mediumint(7) UNSIGNED NOT NULL default '0';

CREATE TABLE IF NOT EXISTS `{:=DBPREFIX=:}flood` (
  `ip` varchar(16) NOT NULL default '',
  `mid` mediumint(7) unsigned NOT NULL default '0',
  `time` int(10) unsigned NOT NULL,
  `type` varchar(3) NOT NULL default 'sta'
) TYPE=MyISAM;

CREATE TABLE IF NOT EXISTS `{:=DBPREFIX=:}documents_content` (
  `did` int(10) unsigned NOT NULL default '0',
  `lid` smallint(4) unsigned NOT NULL default '0',
  `title` varchar(255) NOT NULL default '',
  `content` mediumtext NOT NULL,
  `active` enum('0','1') NOT NULL default '0',
  UNIQUE KEY `id` (`did`,`lid`)
) TYPE=MyISAM;

DROP TABLE `{:=DBPREFIX=:}documents`;
CREATE TABLE `{:=DBPREFIX=:}documents` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `author` mediumint(7) unsigned NOT NULL default '0',
  `date` int(10) unsigned NOT NULL default '0',
  `update` int(10) unsigned NOT NULL default '0',
  `type` tinyint(2) NOT NULL default '0',
  `groups` varchar(150) NOT NULL default '',
  `icomment` text NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM  AUTO_INCREMENT=1 ;


ALTER TABLE `{:=DBPREFIX=:}forums` ADD COLUMN `lid` smallint(4) unsigned NOT NULL default '0' AFTER `message_text`;

ALTER TABLE `{:=DBPREFIX=:}grab` MODIFY COLUMN `entries` smallint(2) unsigned NOT NULL default '0';
ALTER TABLE `{:=DBPREFIX=:}grab` MODIFY COLUMN `max_age` mediumint(6) unsigned NOT NULL default '720';

ALTER TABLE `{:=DBPREFIX=:}language` MODIFY COLUMN `id` smallint(4) unsigned NOT NULL auto_increment;

DROP TABLE IF EXISTS `{:=DBPREFIX=:}spellcheck`;