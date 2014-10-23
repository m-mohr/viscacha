<mail>
	<title>{@config->fname}: Aktivierungsemail</title>
	<comment>Hallo {@_POST->name},

Sie haben sich soeben erfolgreich im Forum "{@config->fname}" registriert.

Um die Registrierung zu bestätigen, besuchen Sie bitte den folgenden Link:
{@config->furl}/register.php?action=confirm&id={$redirect}&fid={$confirmcode}

Der Forenverwalter (Administrator) muss Ihre Registrierung auch erst noch bestätigen, bevor Sie sich einloggen können. Sie erhalten dann eine weitere Bestätigungsemail von uns!

Mit freundlichen Grüßen
Ihr {@config->fname} Team
{@config->furl}
</comment>
</mail>