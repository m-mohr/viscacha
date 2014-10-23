<mail>
	<title>{@config->fname}: New post for topic &quot;{@row->topic}&quot;</title>
	<comment>Hello {@row->name}

You subscribed the topic &quot;{@row->topic}&quot;.
New posts have been made. The last entry is from {@row->last_name}.

You can view the topic here:
{@config->furl}/showtopic.php?id={@row->id}

Best regards,
your {@config->fname} team
{@config->furl}
____________________________________________
To disable notifications, visit your topic subscription administration:
{@config->furl}/editprofile.php?action=abos</comment>
</mail>
