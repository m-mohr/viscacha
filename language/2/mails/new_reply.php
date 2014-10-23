<mail>
	<title>{@config->fname}: New reply in forum &quot;{@last->name}&quot;</title>
	<comment>Hello,

a new reply has just been posted. The reply has the title &quot;{@_POST->topic}&quot; and was created in the forum &quot;{@last->name}&quot;.
You can find the new reply here:
{@config->furl}/showtopic.php?action=jumpto&id={@_POST->id}&topic_id={$redirect}

Best regards,
your {@config->fname} team
{@config->furl}</comment>
</mail>
