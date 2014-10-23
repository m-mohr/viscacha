<mail>
	<title>{@config->fname}: Topic "{@old->topic}" has been moved</title>
	<comment>Dear {@old->name},

your topic "{@old->topic}" has just been moved. You will now find it here: 
{@config->furl}/showtopic.php?id={@info->id}

Best regards,
your {@config->fname} team
{@config->furl}</comment>
</mail>