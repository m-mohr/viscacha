<mail>
	<title>{@config->fname}: Neue Antwort im Thema &quot;{@row->topic}&quot;</title>
	<comment>Hallo {@row->name},

Sie haben das Thema &quot;{@row->topic}&quot; abonniert.
Zu diesem Thema gibt es (mindestens) eine neuen Antwort.

Hier k&ouml;nnen Sie das Thema einsehen:
{@config->furl}/showtopic.php?id={@row->id}&action=firstnew

Mit freundlichen Gr&uuml;&szlig;en,
Ihr {@config->fname} Team
{@config->furl}
_____________________________________________________________________
Um keine E-Mail-Benachrichtigungen mehr zu erhalten, besuchen Sie
bitte die Abonnement-Verwaltung in Ihren persönlichen Einstellungen:
{@config->furl}/editprofile.php?action=abos</comment>
</mail>
