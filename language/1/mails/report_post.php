<mail>
	<title>{@config->fname}: Ein Beitrag wurde gemeldet</title>
	<comment>Hallo {@row->name},

der Beitrag mit dem Titel {@info->title} wurde von {%my->name} gemeldet. Die folgende Nachricht wurde dazu eingegeben:
----------------------------------------------------------------------
{$message}
----------------------------------------------------------------------

Sie k&ouml;nnen den Beitrag hier finden:
{@config->furl}/showtopic.php?action=jumpto&topic_id={@info->id}

Mit freundlichen Gr&uuml;&szlig;en,
Ihr {@config->fname} Team
{@config->furl}</comment>
</mail>
