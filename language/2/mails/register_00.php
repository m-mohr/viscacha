<mail>
	<title>{@config->fname}: activation e-mail</title>
	<comment>Dear {@_POST->name},

You have (or someone using your email address has) just registered at &quot;{@config->fname}&quot;.

To confirm the activation, please follow this link:
{@config->furl}/register.php?action=confirm&id={$redirect}&fid={$confirmcode}

The board administrator has to confirm your registration before you can log in. You will get a new e-mail as soon as your account is activated!

Best regards,
your {@config->fname} team
{@config->furl}
</comment>
</mail>
