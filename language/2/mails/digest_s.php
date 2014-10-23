<mail>
	<title>{@config->fname}: New post in topic "{@row->topic}"</title>
	<comment>Hello {@row->name}

YOu have subscribed the topic "{@row->topic}".
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