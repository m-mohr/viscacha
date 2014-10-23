CREATE TABLE `{:=DBPREFIX=:}settings` (
  `name` varchar(120) NOT NULL default '',
  `title` varchar(150) NOT NULL default '',
  `description` text NOT NULL,
  `type` enum('text','textarea','select','checkbox') NOT NULL default 'text',
  `optionscode` text NOT NULL,
  `value` text NOT NULL,
  `sgroup` smallint(4) unsigned NOT NULL,
  UNIQUE KEY `title` (`title`,`sgroup`)
) TYPE=MyISAM;

INSERT INTO `{:=DBPREFIX=:}settings` (`name`, `title`, `description`, `type`, `optionscode`, `value`, `sgroup`) VALUES ('relatednum', 'Number of smiliar topics', 'Maximum number of topics found as similar topics', 'text', '', '5', 1),
('items', 'Number of news', 'Number of news shown on the frontpage', 'text', '', '5', 2),
('teaserlength', 'Shortening news', 'Determine onto how many signs the preview of the articles is shortened', 'text', '', '300', 2),
('feed', 'ID of Newsfeed', 'ID of Newsfeed to show. Look up ID here: <a href="admin.php?action=cms&job=feed" target="_blank">Newsfeed Syndication</a>', 'text', '', '1', 3),
('title', 'Title for Newsfeed', '', 'text', '', 'Ticker', 3),
('text', 'Text to show', 'You can enter the message here. You can use HTML.', 'textarea', '', '', 4),
('title', 'Title to show', 'You can enter the title here. You can use HTML.', 'text', '', '', 4),
('topicnum', 'Topics to show', 'Number of new topics which are supposed to be listed maximally.', 'text', '', '10', 5),
('repliesnum', 'Number of replies', 'Maximum number of newest replies shown after the form.', 'text', '', '5', 6);