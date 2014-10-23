<mail>
	<title>{@config->fname}: New topic in forum &quot;{@last->name}&quot;</title>
	<comment>Hello,

a new topic has been posted. The topic has the title &quot;{@_POST->topic}&quot; and was created in the forum &quot;{@last->name}&quot;.
You can find the new topic here:
{@config->furl}/showtopic.php?id={$tredirect}

Best regards,
your {@config->fname} team
{@config->furl}</comment>
</mail>
