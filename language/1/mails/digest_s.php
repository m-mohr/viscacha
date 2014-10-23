<mail>
	<title>{@config->fname}: Neuer Beitrag im Thema &quot;{@row->topic}&quot;</title>
	<comment>Hallo {@row->name},

Sie haben das Thema &quot;{@row->topic}&quot; abonniert.
Zu diesem Thema gibt es einen neuen Beitrag. Der Beitrag stammt von {$pname}.

Hier k&ouml;nnen Sie das Thema einsehen: 
{@config->furl}/showtopic.php?id={@row->id}

Mit freundlichen Gr&uuml;&szlig;en,
Ihr {@config->fname} Team
{@config->furl}
____________________________________________
Um keine E-Mail-Benachrichtigungen mehr zu erhalten, besuchen Sie bitte Ihre Themen-Abonnement-Verwaltung:
{@config->furl}/editprofile.php?action=abos</comment>
</mail>
