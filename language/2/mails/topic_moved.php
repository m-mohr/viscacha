<mail>
	<title>{@config->fname}: Topic &quot;{@old->topic}&quot; has been moved</title>
	<comment>Dear {@old->name},

your topic &quot;{@old->topic}&quot; has just been moved. You can now find it here:
{@config->furl}/showtopic.php?id={@info->id}

Best regards,
your {@config->fname} team
{@config->furl}</comment>
</mail>
