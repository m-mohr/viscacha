<mail>
	<title>{@config->fname}: Neue Antwort im Forum &quot;{@last->name}&quot;</title>
	<comment>Hallo,

soeben wurde eine neue Antwort geschrieben. Die Antwort hat den Titel &quot;{@_POST->topic}&quot; und ist im Forum &quot;{@last->name}&quot; verfasst worden.
Sie finden die neue Antwort hier:
{@config->furl}/showtopic.php?action=jumpto&topic_id={$redirect}

Mit freundlichen Gr&uuml;&szlig;en,
Ihr {@config->fname} Team
{@config->furl}</comment>
</mail>
