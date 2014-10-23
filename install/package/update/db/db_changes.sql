ALTER TABLE `{:=DBPREFIX=:}bbcode` MODIFY COLUMN `bbcodeexample` text NOT NULL;
ALTER TABLE `{:=DBPREFIX=:}bbcode` MODIFY COLUMN `title` varchar(255) NOT NULL default '';

ALTER TABLE `{:=DBPREFIX=:}categories` MODIFY COLUMN `name` text NOT NULL;

ALTER TABLE `{:=DBPREFIX=:}filetypes` MODIFY COLUMN `program` text NOT NULL;

ALTER TABLE `{:=DBPREFIX=:}forums` MODIFY COLUMN `name` text NOT NULL;
ALTER TABLE `{:=DBPREFIX=:}forums` MODIFY COLUMN `message_title` text NOT NULL;

ALTER TABLE `{:=DBPREFIX=:}pm` MODIFY COLUMN `topic` text NOT NULL;

ALTER TABLE `{:=DBPREFIX=:}profilefields` MODIFY COLUMN `name` varchar(200) NOT NULL default '';

ALTER TABLE `{:=DBPREFIX=:}replies` MODIFY COLUMN `topic` text NOT NULL;
ALTER TABLE `{:=DBPREFIX=:}replies` MODIFY COLUMN `report` text NOT NULL;

ALTER TABLE `{:=DBPREFIX=:}session` MODIFY COLUMN `pwfaccess` text NOT NULL;
ALTER TABLE `{:=DBPREFIX=:}session` MODIFY COLUMN `settings` text NOT NULL;

ALTER TABLE `{:=DBPREFIX=:}settings` MODIFY COLUMN `title` varchar(255) NOT NULL default '';

ALTER TABLE `{:=DBPREFIX=:}settings_groups` MODIFY COLUMN `title` varchar(255) NOT NULL default '';

ALTER TABLE `{:=DBPREFIX=:}textparser` MODIFY COLUMN `id` mediumint(6) unsigned NOT NULL auto_increment;
ALTER TABLE `{:=DBPREFIX=:}textparser` MODIFY COLUMN `search` varchar(255) NOT NULL default '';
ALTER TABLE `{:=DBPREFIX=:}textparser` MODIFY COLUMN `replace` varchar(255) NOT NULL default '';
ALTER TABLE `{:=DBPREFIX=:}textparser` MODIFY COLUMN `desc` text NOT NULL;

ALTER TABLE `{:=DBPREFIX=:}topics` MODIFY COLUMN `topic` text NOT NULL;
ALTER TABLE `{:=DBPREFIX=:}topics` MODIFY COLUMN `name` varchar(255) NOT NULL default '';
ALTER TABLE `{:=DBPREFIX=:}topics` MODIFY COLUMN `last_name` varchar(255) NOT NULL default '';
ALTER TABLE `{:=DBPREFIX=:}topics` MODIFY COLUMN `vquestion` text NOT NULL;
ALTER TABLE `{:=DBPREFIX=:}topics` MODIFY COLUMN `mark` enum('','b','g','a','n') NULL default NULL;

ALTER TABLE `{:=DBPREFIX=:}user` MODIFY COLUMN `name` varchar(255) NOT NULL default '';
ALTER TABLE `{:=DBPREFIX=:}user` MODIFY COLUMN `fullname` varchar(255) NOT NULL default '';
ALTER TABLE `{:=DBPREFIX=:}user` MODIFY COLUMN `location` varchar(200) NOT NULL default '';

ALTER TABLE `{:=DBPREFIX=:}vote` MODIFY COLUMN `answer` text NOT NULL;