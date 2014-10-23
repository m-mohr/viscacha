<?php
include('../data/config.inc.php');
$ignore = isset($_REQUEST['ignore']) ? $_REQUEST['ignore'] : null;
if ($config['version'] != '0.8 Beta 1' && empty($ignore)) { ?>
<div class="bbody">
<p><strong>Welcome to the Viscacha update wizard.</strong></p>
<p>The currently installed version of Viscacha (<?php echo $config['version']; ?>) is not compatible with this update or is already installed! You can skip this error, but we do not recommend to proceed with this update. </p>
</div>
<div class="bfoot center"><input type="submit" name="ignore" value="Ignore" /></div>
<?php } else { ?>
<div class="bbody">
<p><strong>Welcome to the Viscacha update wizard.</strong></p>
<p>
This wizard guides you step by step through this update and modifies your Viscacha to version <?php echo VISCACHA_VERSION; ?>. 
<em>Please make a complete backup before continuing with the update!</em> 
For support or a detailed documentation vist the <a href="http://docs.viscacha.org">Viscacha project page</a>.
</p>
<p>To continue click the &quot;Continue&quot; button below.</p>
</div>
<div class="bfoot center"><input type="submit" value="Continue" /></div>
<?php } ?>