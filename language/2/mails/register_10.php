<mail>
	<title>{@config->fname}: Activation e-mail</title>
	<comment>Dear {@_POST->name},

You just have registered at the board "{@config->fname}".

To confirm you registration, please follow this link:
{@config->furl}/register.php?action=confirm&id={$redirect}&fid={$confirmcode}
After that you will be able to login with your username and password.

Best regards,
your {@config->fname} team
{@config->furl}
</comment>
</mail>
