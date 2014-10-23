<mail>
	<title>{@config->fname}: New reply in forum "{@last->name}"</title>
	<comment>Hello,

a new reply was posted right now. The reply has the title "{@_POST->topic}" and was created in the forum "{@last->name}".
You can find the new reply here:
{@config->furl}/showtopic.php?action=jumpto&id={@_POST->id}&topic_id={$redirect}

Best regards,
your {@config->fname} team
{@config->furl}</comment>
</mail>