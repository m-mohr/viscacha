<mail>
	<title>{@config->fname}: Neue Antwort im Forum "{@last->name}"</title>
	<comment>Hallo,

soeben wurde eine neue Antwort geschrieben. Die Antwort hat den Titel "{@_POST->topic}" und ist im Forum "{@last->name}" verfasst worden.
Du findest die neue Antwort hier:
{@config->furl}/showtopic.php?action=jumpto&id={@_POST->id}&topic_id={$redirect}

Mit freundlichen Grüßen,
Dein {@config->fname} Team
{@config->furl}</comment>
</mail>