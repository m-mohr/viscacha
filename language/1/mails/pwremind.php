<mail>
	<title>{@config->fname}: Best&auml;tigung deiner Passwortanfrage</title>
	<comment>Hallo {@user->name},

Sie erhalten diese E-Mail, weil Sie (oder jemand der sich als Sie ausgibt) ein neues Passwort f&uuml;r Ihren Account auf {@config->fname} angefordert hat. Wenn Sie kein neues Passwort angefordert haben, ignorieren Sie diese E-Mail bitte. Im Falle, dass Sie weitere unerw&uuml;nschte E-Mails dieser Sorte bekommen sollten, wenden Sie sich bitte an den Administrator.

Um ein neues Passwort zu erhalten, m&uuml;ssen Sie diese E-Mail best&auml;tigen. Um dies zu tun, klicken Sie bitte den Link unterhalb. Wenn Sie diese Seite besuchen, wird Ihr Passwort ge&auml;ndert und das neue Passwort wird Ihnen dann per E-Mail zugeschickt.

{@config->furl}/log.php?action=pwremind3&id={@user->id}&fid={$confirmcode}

Mit freundlichen Gr&uuml;&szlig;en,
Ihr {@config->fname} Team
{@config->furl}</comment>
</mail>
