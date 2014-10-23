<mail>
	<title>{@config->fname}: Neues Mitglied im Forum "{@config->fname}"</title>
	<comment>Hallo,

soeben hat sich ein neues Mitglied registriert. Das Mitglied heißt "{@_POST->name}".
Sie finden das Profil des Mitglieds hier:
{@config->furl}/profile.php?id={$redirect}

Mit freundlichen Grüßen,
Ihr {@config->fname} Team
{@config->furl}</comment>
</mail>