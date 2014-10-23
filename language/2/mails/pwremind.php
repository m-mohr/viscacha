<mail>
	<title>{@config->fname}: Confirmation of your password request</title>
	<comment>Dear {@user->name},

You are receiving this e-mail because you have (or someone pretending to be you has) requested a new password be sent for your account on {@config->fname}. If you did not request this e-mail then please ignore it, if you keep receiving it please contact the board admin.

To set a new password you need to confirm this e-mail it. To do this click the link provided below. When you visit that page, your password will be reset, and the new password will be mailed to you. 

{@config->furl}/log.php?action=pwremind3&id={@user->id}&fid={$confirmcode}

Best regards,
your {@config->fname} team
{@config->furl}</comment>
</mail>
