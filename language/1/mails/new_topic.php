<mail>
	<title>{@config->fname}: Neues Thema im Forum &quot;{@last->name}&quot;</title>
	<comment>Hallo,

soeben wurde ein neues Thema gestartet. Das Thema hat den Titel &quot;{@_POST->topic}&quot; und ist im Forum &quot;{@last->name}&quot; erstellt worden.
Sie finden das neue Thema hier:
{@config->furl}/showtopic.php?id={$tredirect}

Mit freundlichen Gr&uuml;&szlig;en,
Ihr {@config->fname} Team
{@config->furl}</comment>
</mail>
