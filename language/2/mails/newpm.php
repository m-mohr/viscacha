<mail>
	<title>{@config->fname}: New PM received</title>
	<comment>Dear {@row->name}

You just reveived a new private message. 
The PM is from {%my->name} and has the title &quot;{@_POST->topic}&quot;.
You can view the PM with this link:
{@config->furl}/pm.php

Best regards,
your {@config->fname} team
{@config->furl}</comment>
</mail>
