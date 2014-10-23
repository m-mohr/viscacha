<mail>
	<title>{@config->fname}: Aktivierungs-E-Mail</title>
	<comment>Hallo {@_POST->name},

Sie haben sich soeben erfolgreich im Forum "{@config->fname}" registriert.

Um die Registrierung zu bestätigen, besuchen Sie bitte den folgenden Link:
{@config->furl}/register.php?action=confirm&id={$redirect}&fid={$confirmcode}
Danach können Sie sich mit Ihren Nutzerdaten im Forum anmelden.

Mit freundlichen Grüßen,
Ihr {@config->fname} Team
{@config->furl}
</comment>
</mail>
