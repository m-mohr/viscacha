<mail>
	<title>{@config->fname}: A post has been reported</title>
	<comment>Hello {@row->name},

the post with the title {@info->title} has been reported by {%my->name}. The following message has been added:
----------------------------------------------------------------------
{$message}
----------------------------------------------------------------------

You can find the post here:
{@config->furl}/showtopic.php?action=jumpto&topic_id={@info->id}

Yours sincerely,
Your {@config->fname} Team
{@config->furl}</comment>
</mail>