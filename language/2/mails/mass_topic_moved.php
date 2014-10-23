<mail>
	<title>{@config->fname}: Topic &quot;{@old->topic}&quot; has been moved</title>
	<comment>Dear {@old->name}. 

Your topic &quot;{@old->topic}&quot; has just been moved. You can find it at the new location:
{@config->furl}/showtopic.php?id={$id}

Best regards,
your {@config->fname} team
{@config->furl}</comment>
</mail>
