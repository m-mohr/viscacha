<mail>
	<title>{@config->fname}: New post in topic &quot;{@row->topic}&quot;</title>
	<comment>Hello {@row->name}

You have subscribed to the topic &quot;{@row->topic}&quot;.
There are new posts in this topic. The last post is from {@row->last_name}.

You can view the topic here:
{@config->furl}/showtopic.php?id={@row->id}

Best regards,
your {@config->fname} team
{@config->furl}
____________________________________________
To disable notifications, visit your topic subscription administration:
{@config->furl}/editprofile.php?action=abos</comment>
</mail>
