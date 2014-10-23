<mail>
	<title>{@config->fname}: Aktivierungs-E-Mail</title>
	<comment>Hallo {@_POST->name},

Sie haben sich soeben erfolgreich im Forum &quot;{@config->fname}&quot; registriert.

Um die Registrierung zu best&auml;tigen, besuchen Sie bitte den folgenden Link:
{@config->furl}/register.php?action=confirm&id={$redirect}&fid={$confirmcode}
Danach k&ouml;nnen Sie sich mit Ihren Nutzerdaten im Forum anmelden.

Mit freundlichen Gr&uuml;&szlig;en,
Ihr {@config->fname} Team
{@config->furl}
</comment>
</mail>
