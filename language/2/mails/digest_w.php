<mail>
	<title>{@config->fname}: New reply in topic &quot;{@row->topic}&quot;</title>
	<comment>Hello {@row->name}

You have subscribed to the topic &quot;{@row->topic}&quot;.
There is at least one new reply for this topic.
The last reply was written by {@row->last_name}.

You can view the topic here:
{@config->furl}/showtopic.php?id={@row->id}&action=firstnew

Best regards,
your {@config->fname} team
{@config->furl}
_____________________________________________________________________
To disable e-mail notifications, please visit your subscriptions
in your User Control Panel:
{@config->furl}/editprofile.php?action=abos</comment>
</mail>
