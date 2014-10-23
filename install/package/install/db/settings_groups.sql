CREATE TABLE `{:=DBPREFIX=:}settings_groups` (
  `id` smallint(4) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL default '',
  `name` varchar(120) NOT NULL default '',
  `description` tinytext NOT NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM  PACK_KEYS=0 AUTO_INCREMENT=6 ;

INSERT INTO `{:=DBPREFIX=:}settings_groups` (`id`, `title`, `name`, `description`) VALUES
(1, 'Related Topics', 'viscacha_related_topics', 'General settings related to the "Related Topics" package.'),
(2, 'News Boxes', 'viscacha_news_boxes', 'Configuration of the news boxes for the portal'),
(3, 'Last replies', 'viscacha_addreply_last_replies', 'Configure the package that shows the last replies.'),
(4, 'Recent Topics', 'viscacha_recent_topics', 'Configuration for Recent Topics Package.'),
(5, 'Document on Portal', 'viscacha_document_on_portal', 'Displays a document on the portal.');
