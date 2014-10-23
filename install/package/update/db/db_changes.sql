ALTER TABLE `{:=DBPREFIX=:}component` MODIFY COLUMN `file` varchar(127) NOT NULL DEFAULT '' AFTER `id`;
ALTER TABLE `{:=DBPREFIX=:}component` ADD COLUMN `package` mediumint(7) unsigned NOT NULL AFTER `file`;
ALTER TABLE `{:=DBPREFIX=:}component` MODIFY COLUMN `active` enum('0','1') NOT NULL DEFAULT '1' AFTER `package`;
ALTER TABLE `{:=DBPREFIX=:}component` ADD COLUMN `required` enum('0','1') NOT NULL DEFAULT '1' AFTER `active`;

ALTER TABLE `{:=DBPREFIX=:}filetypes` MODIFY COLUMN `extension` varchar(200) NOT NULL DEFAULT '' AFTER `id`;
ALTER TABLE `{:=DBPREFIX=:}filetypes` MODIFY COLUMN `icon` varchar(100) NOT NULL DEFAULT '' AFTER `desctxt`;
ALTER TABLE `{:=DBPREFIX=:}filetypes` MODIFY COLUMN `mimetype` varchar(100) NOT NULL DEFAULT 'application/octet-stream' AFTER `icon`;

ALTER TABLE `{:=DBPREFIX=:}menu` ADD COLUMN `position` varchar(10) NOT NULL DEFAULT 'left' AFTER `groups`;

ALTER TABLE `{:=DBPREFIX=:}newsletter` MODIFY COLUMN `receiver` longtext NOT NULL AFTER `id`;
ALTER TABLE `{:=DBPREFIX=:}newsletter` ADD COLUMN `sender` varchar(255) NOT NULL DEFAULT '' AFTER `title`;
ALTER TABLE `{:=DBPREFIX=:}newsletter` ADD COLUMN `type` enum('p','h') NULL DEFAULT 'p' AFTER `time`;

ALTER TABLE `{:=DBPREFIX=:}packages` ADD COLUMN `active` enum('0','1') NOT NULL DEFAULT '0' AFTER `title`;
ALTER TABLE `{:=DBPREFIX=:}packages` ADD COLUMN `version` varchar(64) NOT NULL DEFAULT '' AFTER `active`;
ALTER TABLE `{:=DBPREFIX=:}packages` ADD COLUMN `internal` varchar(100) NOT NULL DEFAULT '' AFTER `version`;
ALTER TABLE `{:=DBPREFIX=:}packages` ADD COLUMN `core` enum('0','1') NOT NULL DEFAULT '0' AFTER `internal`;

ALTER TABLE `{:=DBPREFIX=:}plugins` MODIFY COLUMN `active` enum('0','1') NOT NULL DEFAULT '1' AFTER `ordering`;
ALTER TABLE `{:=DBPREFIX=:}plugins` ADD COLUMN `required` enum('0','1') NOT NULL DEFAULT '1' AFTER `position`;

ALTER TABLE `{:=DBPREFIX=:}replies` ADD COLUMN `report` tinytext NOT NULL AFTER `edit`;

TRUNCATE TABLE `{:=DBPREFIX=:}component`;
TRUNCATE TABLE `{:=DBPREFIX=:}settings`;
TRUNCATE TABLE `{:=DBPREFIX=:}settings_groups`;
TRUNCATE TABLE `{:=DBPREFIX=:}plugins`;
TRUNCATE TABLE `{:=DBPREFIX=:}packages`;

DELETE FROM `{:=DBPREFIX=:}menu` WHERE module != '0';
INSERT INTO `{:=DBPREFIX=:}menu` (`name`, `link`, `param`, `groups`, `position`, `ordering`, `sub`, `module`, `active`) VALUES
('Personal Box', '', '', '0', 'left', 0, 0, 15, '1');

INSERT INTO `{:=DBPREFIX=:}packages` (`id`, `title`, `active`, `version`, `internal`, `core`) VALUES
(1, 'Related Topics', '1', '0.8', 'viscacha_related_topics', '0'),
(2, 'Who is online', '1', '0.8', 'viscacha_who_is_online', '0'),
(3, 'News Boxes', '1', '0.8', 'viscacha_news_boxes', '0'),
(4, 'Last replies', '1', '0.8', 'viscacha_addreply_last_replies', '0'),
(5, 'Legends', '1', '0.8', 'viscacha_help_legends', '0'),
(6, 'Personal Panels', '1', '0.8', 'viscacha_personal_panels', '0'),
(7, 'Quick Reply', '1', '0.8', 'viscacha_quick_reply', '0'),
(8, 'Recent Topics', '1', '0.8', 'viscacha_recent_topics', '0');

INSERT INTO `{:=DBPREFIX=:}plugins` (`id`, `name`, `module`, `ordering`, `active`, `position`, `required`) VALUES
(1, 'Related Topics', 1, 1, '1', 'showtopic_end', '0'),
(2, 'Who is online Box', 2, 1, '1', 'forum_end', '0'),
(3, 'Who is online Data Preparation', 2, 1, '1', 'frontend_init', '1'),
(4, 'Who is online for Navigation', 2, 1, '1', 'navigation', '0'),
(5, 'News Boxes', 3, 1, '1', 'portal', '0'),
(6, 'Last replies', 4, 1, '1', 'addreply_form_end', '0'),
(7, 'Last reply for Private Messages', 4, 1, '1', 'pm_compose_end', '0'),
(8, 'Legend for PMs', 5, 1, '1', 'pm_browse_end', '0'),
(9, 'Legend for PMs', 5, 1, '1', 'pm_index_end', '0'),
(10, 'Legend for Topics', 5, 1, '1', 'showforum_end', '0'),
(11, 'Legend for Topics', 5, 1, '1', 'search_result_end', '0'),
(12, 'Legend for Topics', 5, 1, '1', 'search_active_end', '0'),
(13, 'Legend for Forums', 5, 2, '1', 'forum_end', '0'),
(14, 'Legend for Topics', 5, 1, '1', 'editprofile_mylast_end', '0'),
(15, 'Personal Box for Navigation', 6, 2, '1', 'navigation', '0'),
(16, 'Login Box', 6, 1, '1', 'forum_start', '0'),
(17, 'Quick Reply Form', 7, 2, '1', 'showtopic_end', '0'),
(18, 'Quick Reply Extended Switcher', 7, 1, '1', 'addreply_save_errorhandling', '1'),
(19, 'Recent Topics', 8, 2, '1', 'portal', '0'),
(20, 'Recent Topics', 8, 3, '1', 'forum_end', '0'),
(21, 'PM Notification', 6, 0, '1', 'template_forum_menu', '0');

INSERT INTO `{:=DBPREFIX=:}settings_groups` (`id`, `title`, `name`, `description`) VALUES
(1, 'Related Topics', 'viscacha_related_topics', 'General settings related to the "Related Topics" package.'),
(2, 'News Boxes', 'viscacha_news_boxes', 'Configuration of the news boxes for the portal'),
(3, 'Last replies', 'viscacha_addreply_last_replies', 'Configure the package that shows the last replies.'),
(4, 'Recent Topics', 'viscacha_recent_topics', 'Configuration for Recent Topics Package.');

INSERT INTO `{:=DBPREFIX=:}settings` (`name`, `title`, `description`, `type`, `optionscode`, `value`, `sgroup`) VALUES
('relatednum', 'Maximum number of similar topics', 'The maximum number of related topics that will be shown per topic.', 'text', '', '5', 1),
('hide_empty', 'Hide empty box', 'Hide the box if no related topic is found', 'checkbox', '', '1', 1),
('items', 'Number of news', 'Number of news shown on the frontpage', 'text', '', '5', 2),
('teaserlength', 'Cut news after', 'Determine after how many chars the news are shortened (only if no code to cut the text is specified).', 'text', '', '300', 2),
('cutat', 'Code to cut after', 'You can cut the preview (teaser) manually by placing the specified code in the text. All text after this code will be removed from the preview.', 'select', 'teaser=[teaser]', 'teaser', 2),
('repliesnum', 'Number of replies', 'Maximum number of (newest) replies that will be shown after the form for new replies to topics (Addreply).', 'text', '', '5', 3),
('topicnum', 'Topics to show', 'Number of new topics which are supposed to be listed maximally.', 'text', '', '10', 4);
