<mail>
	<title>{@config->fname}: Aktivierungs-E-Mail</title>
	<comment>Hallo {@_POST->name},

Du hast dich soeben erfolgreich im Forum "{@config->fname}" registriert.

Um die Registrierung zu bestätigen, besuche bitte den folgenden Link:
{@config->furl}/register.php?action=confirm&id={$redirect}&fid={$confirmcode}

Der Forenverwalter (Administrator) muss Deine Registrierung auch erst noch bestätigen, bevor Du dich einloggen kannst. Du erhälst dann eine weitere Bestätigungs-E-Mail von uns!

Mit freundlichen Grüßen,
Dein {@config->fname} Team
{@config->furl}
</comment>
</mail>
