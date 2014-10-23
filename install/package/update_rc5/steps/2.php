<?php
$dataGiven = false;
include('data/config.inc.php');
require_once("install/classes/ftp/class.ftp.php");
require_once("install/classes/ftp/class.ftp_".pemftp_class_module().".php");

$ftp = new ftp(false, false);
if($ftp->SetServer($config['ftp_server'], $config['ftp_port'])) {
	if ($ftp->connect()) {
		if ($ftp->login($config['ftp_user'], $config['ftp_pw'])) {
			if ($ftp->chdir($config['ftp_path']) && $ftp->file_exists('data/config.inc.php')) {
				$dataGiven = true;
			}
		}
	}
	$ftp->quit();
}
?>
<div class="bbody">
<p>
Before we start the automatic update (file updates, updating CHMODs), you have to read the manual update instructions.
Please follow the steps and do the tasks.
More Information:
<?php if (file_exists('_docs/readme.txt')) { ?>
<a href="_docs/readme.txt" target="_blank">_docs/readme.txt</a>
<?php } else { ?>
_docs/readme.txt
<?php } ?>
</p>
<p>
<strong>Update instructions:</strong><br />
<ol class="upd_instr">
<li>Make sure you have a <b>complete backup of your data</b> (FTP + MySQL)!</li>
<li><b>You need to specified the ftp data in your Admin Control Panel</b> before you continue with the next step or the CHMODs can't be set correctly!</li>

<li>Open the file <b>designs/*/ie.css</b>:<br />
<em>You have to apply the following changes (for all CSS files) to all your installed designs. * is a placeholder for a Design-ID (1,2,3,...). The CSS definitions can vary depending on your modifications to the styles.</em>
<ol>
<li>
Search and delete:<br />
<code>* html .editor_textarea_outer .popup {<br />
&nbsp;&nbsp;&nbsp;&nbsp;border-top-width: 0px;<br />
}<br />
* html .editor_textarea_outer .popup strong {<br />
&nbsp;&nbsp;&nbsp;&nbsp;border-width: 0px;<br />
&nbsp;&nbsp;&nbsp;&nbsp;border-top: 1px solid #888888;<br />
}<br />
* html .editor_textarea_outer .popup li {<br />
&nbsp;&nbsp;&nbsp;&nbsp;border-top: 1px solid #c4c4c4;<br />
}
</code>
</li>
</ol>
</li>
</li>

<li>Open the file <b>designs/*/standard.css</b>:
<ol>
<li>
Search:<br />
<code>.bb_blockcode li {<br />
&nbsp;&nbsp;&nbsp;&nbsp;white-space: pre;<br />
&nbsp;&nbsp;&nbsp;&nbsp;font-family: 'Courier New', monospace;<br />
&nbsp;&nbsp;&nbsp;&nbsp;font-weight: normal;<br />
&nbsp;&nbsp;&nbsp;&nbsp;font-style: normal;<br />
&nbsp;&nbsp;&nbsp;&nbsp;margin-left: 4px;<br />
}
</code>
Replace with:<br />
<code>.bb_blockcode * {<br />
&nbsp;&nbsp;&nbsp;&nbsp;font-family: 'Courier New', monospace;<br />
}<br />
.bb_blockcode li {<br />
&nbsp;&nbsp;&nbsp;&nbsp;margin-left: 12px;<br />
&nbsp;&nbsp;&nbsp;&nbsp;white-space: pre;<br />
}
</code>
</li>

<li>
Search:<br />
<code>.editor_textarea_inner {<br />
&nbsp;&nbsp;&nbsp;&nbsp;background-color: #FFFFFF;<br />
&nbsp;&nbsp;&nbsp;&nbsp;font-size: 9pt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;border-width: 0px;<br />
&nbsp;&nbsp;&nbsp;&nbsp;width: 100%;<br />
&nbsp;&nbsp;&nbsp;&nbsp;overflow: auto;<br />
&nbsp;&nbsp;&nbsp;&nbsp;margin: -4px;<br />
&nbsp;&nbsp;&nbsp;&nbsp;padding: 4px;<br />
}
</code>
In this part of the stylesheet delete:<br />
<code>&nbsp;&nbsp;&nbsp;&nbsp;overflow: auto;</code>
</li>
<li>
Search:<br />
<code>.editor_textarea_outer .popup {</code>
Add below:<br />
<code>&nbsp;&nbsp;&nbsp;&nbsp;overflow: auto;</code>
</li>

<li>
Search:<br />
<code>.bbcolor {<br />
&nbsp;&nbsp;&nbsp;&nbsp;padding: 10px;<br />
&nbsp;&nbsp;&nbsp;&nbsp;background-color: #ffffff;<br />
&nbsp;&nbsp;&nbsp;&nbsp;line-height: 13px;<br />
&nbsp;&nbsp;&nbsp;&nbsp;font-size: 13px;<br />
}<br />
.bbcolor span {<br />
&nbsp;&nbsp;&nbsp;&nbsp;width: 10px;<br />
&nbsp;&nbsp;&nbsp;&nbsp;height: 13px;<br />
&nbsp;&nbsp;&nbsp;&nbsp;display: block;<br />
&nbsp;&nbsp;&nbsp;&nbsp;float: left;<br />
&nbsp;&nbsp;&nbsp;&nbsp;cursor: pointer;<br />
}<br />
.bbcolor img {<br />
&nbsp;&nbsp;&nbsp;&nbsp;width: 10px;<br />
&nbsp;&nbsp;&nbsp;&nbsp;height: 13px;<br />
&nbsp;&nbsp;&nbsp;&nbsp;border-width: 0px;<br />
}<br />
.bbcolor img:hover {<br />
&nbsp;&nbsp;&nbsp;&nbsp;width: 8px;<br />
&nbsp;&nbsp;&nbsp;&nbsp;height: 11px;<br />
&nbsp;&nbsp;&nbsp;&nbsp;border: 1px solid #ffffff;<br />
}</code>
Replace with:<br />
<code>.bbcolor {<br />
&nbsp;&nbsp;&nbsp;&nbsp;padding: 10px;<br />
&nbsp;&nbsp;&nbsp;&nbsp;background-color: #ffffff;<br />
&nbsp;&nbsp;&nbsp;&nbsp;line-height: 12px;<br />
&nbsp;&nbsp;&nbsp;&nbsp;font-size: 12px;<br />
}<br />
.bbcolor img {<br />
&nbsp;&nbsp;&nbsp;&nbsp;width: 8px;<br />
&nbsp;&nbsp;&nbsp;&nbsp;height: 10px;<br />
&nbsp;&nbsp;&nbsp;&nbsp;display: block;<br />
&nbsp;&nbsp;&nbsp;&nbsp;float: left;<br />
&nbsp;&nbsp;&nbsp;&nbsp;cursor: pointer;<br />
&nbsp;&nbsp;&nbsp;&nbsp;border-width: 1px;<br />
&nbsp;&nbsp;&nbsp;&nbsp;border-style: solid;<br />
}</code>
</li>

</ol>
</li>
<li>After the update <b>check for updates of your installed packages</b> in the ACP!</li>
</ol>
</p>
</div>
<div class="bfoot center">
<?php if ($dataGiven) { ?>
<input type="submit" value="Continue" />
<?php } else { ?>
You need to specified correct ftp data in your <a href="../admin/" target="_blank">Admin Control Panel</a> (Viscacha Settings > FTP) before you continue with the next step!<br />
<a class="submit" href="index.php?package=<?php echo $package;?>&amp;step=<?php echo $step; ?>">Try again</a>
<?php } ?>
</div>