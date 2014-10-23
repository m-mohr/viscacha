<mail>
	<title>{@config->fname}: Neuer Beitrag im Thema "{@row->topic}"</title>
	<comment>Hallo {@row->name},

Du hast das Thema "{@row->topic}" abonniert.
Zu diesem Thema gibt es neue Beiträge. Der letzte Beitrag stammt von {@row->last_name}.

Hier kannst Du das Thema einsehen: 
{@config->furl}/showtopic.php?id={@row->id}

Mit freundlichen Grüßen,
Dein {@config->fname} Team
{@config->furl}
____________________________________________
Um keine E-Mail-Benachrichtigungen mehr zu erhalten, besuche bitte Deine Themen-Abonnement-Verwaltung:
{@config->furl}/editprofile.php?action=abos</comment>
</mail>