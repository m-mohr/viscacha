<mail>
	<title>{@config->fname}: New topic in forum "{@last->name}"</title>
	<comment>Hello,

a new topic was started right now. The topic has the title "{@_POST->topic}" and was created in the forum "{@last->name}".
You can find the new topic here:
{@config->furl}/showtopic.php?id={$tredirect}

Best regards,
your {@config->fname} team
{@config->furl}</comment>
</mail>