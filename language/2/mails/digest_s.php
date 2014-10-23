<mail>
	<title>{@config->fname}: New post in topic &quot;{@row->topic}&quot;</title>
	<comment>Hello {@row->name}

You have subscribed the topic &quot;{@row->topic}&quot;.
There is a new post for this topic. The post is from {$pname}.

Here you can view the topic: 
{@config->furl}/showtopic.php?id={@row->id}

Best regards,
your {@config->fname} team
{@config->furl}
____________________________________________
To disable notifications, visit your topic subscription administration:
{@config->furl}/editprofile.php?action=abos</comment>
</mail>
