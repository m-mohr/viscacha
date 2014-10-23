<mail>
	<title>{@config->fname}: New member in forum "{@config->fname}"</title>
	<comment>Hello,

a new member has just been registered The new member is called "{@_POST->name}".
You can find the profile of the new member here:
{@config->furl}/profile.php?id={$redirect}

Best regards,
your {@config->fname} team
{@config->furl}</comment>
</mail>