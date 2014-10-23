<mail>
	<title>{@config->fname}: Neues Thema im Forum "{@last->name}"</title>
	<comment>Hallo,

soeben wurde ein neues Thema gestartet. Das Thema hat den Titel "{@_POST->topic}" und ist im Forum "{@last->name}" erstellt worden.
Sie finden das neue Thema hier:
{@config->furl}/showtopic.php?id={$tredirect}

Mit freundlichen Grüßen,
Ihr {@config->fname} Team
{@config->furl}</comment>
</mail>