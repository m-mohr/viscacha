<mail>
	<title>{@config->fname}: New posting in topic "{@row->topic}"</title>
	<comment>Hello {@row->name}

You have subscribed the topic "{@row->topic}".
There are new postings for this topic. The last posting is from {@row->last_name}.

Here you can view the topic: 
{@config->furl}/showtopic.php?id={@row->id}

Best regards,
your {@config->fname} team
{@config->furl}
____________________________________________
TO disable notifications, visit your topic subscription administration:
{@config->furl}/editprofile.php?action=abos</comment>
</mail>