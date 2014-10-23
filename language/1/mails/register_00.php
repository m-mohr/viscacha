<mail>
	<title>{@config->fname}: Aktivierungs-E-Mail</title>
	<comment>Hallo {@_POST->name},

Sie haben sich soeben erfolgreich im Forum &quot;{@config->fname}&quot; registriert.

Um die Registrierung zu best&auml;tigen, besuchen Sie bitte den folgenden Link:
{@config->furl}/register.php?action=confirm&id={$redirect}&fid={$confirmcode}

Der Forenverwalter (Administrator) muss Ihre Registrierung auch erst noch best&auml;tigen, bevor Sie sich einloggen k&ouml;nnen. Sie erhalten dann eine weitere Best&auml;tigungs-E-Mail von uns!

Mit freundlichen Gr&uuml;&szlig;en,
Ihr {@config->fname} Team
{@config->furl}
</comment>
</mail>
