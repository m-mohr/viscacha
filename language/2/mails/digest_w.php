<mail>
	<title>{@config->fname}: New post in topic "{@row->topic}"</title>
	<comment>Hello {@row->name}

You have subscribed the topic "{@row->topic}".
There are new posts in this topic. The last post is from {@row->last_name}.

Here you can view the topic:
{@config->furl}/showtopic.php?id={@row->id}

Best regards,
your {@config->fname} team
{@config->furl}
____________________________________________
TO disable notifications, visit your topic subscription administration:
{@config->furl}/editprofile.php?action=abos</comment>
</mail>