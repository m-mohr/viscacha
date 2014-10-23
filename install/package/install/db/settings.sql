CREATE TABLE `{:=DBPREFIX=:}settings` (
  `name` varchar(120) NOT NULL default '',
  `title` varchar(255) NOT NULL default '',
  `description` text NOT NULL,
  `type` enum('text','textarea','select','checkbox') NOT NULL default 'text',
  `optionscode` text NOT NULL ,
  `value` text NOT NULL,
  `sgroup` smallint(4) unsigned NOT NULL default '0',
  UNIQUE KEY `title` (`title`,`sgroup`)
) ENGINE=MyISAM;

INSERT INTO `{:=DBPREFIX=:}settings` (`name`, `title`, `description`, `type`, `optionscode`, `value`, `sgroup`) VALUES
('relatednum', 'Maximum number of similar topics', 'The maximum number of related topics that will be shown per topic.', 'text', '', '5', 1),
('hide_empty', 'Hide empty box', 'Hide the box if no related topic is found', 'checkbox', '', '1', 1),
('items', 'Number of news', 'Number of news shown on the frontpage', 'text', '', '5', 2),
('teaserlength', 'Cut news after', 'Determine after how many chars the news are shortened (only if no code to cut the text is specified).', 'text', '', '300', 2),
('cutat', 'Code to cut after', 'You can cut the preview (teaser) manually by placing the specified code in the text. All text after this code will be removed from the preview.', 'select', 'teaser=[teaser]', 'teaser', 2),
('repliesnum', 'Number of replies', 'Maximum number of (newest) replies that will be shown after the form for new replies to topics (Addreply).', 'text', '', '5', 3),
('topicnum', 'Topics to show', 'Number of new topics which are supposed to be listed maximally.', 'text', '', '10', 4),
('doc_id', 'ID of the Document', 'Type in the ID of the document you want to show on the portal. 0 = No document\r\nThe ID is shown in the address of the document. Example: http://www.domain.de/viscacha/docs.php?id=&lt;ID&gt;', 'text', '', '0', 5);
