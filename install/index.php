<?php
error_reporting(E_ALL);

chdir('../');

define('VISCACHA_VERSION', '0.8 RC5');
define('VISCACHA_CORE', '1');
define('SCRIPTNAME', 'install');
define('SCRIPT_LOCKED', file_exists('./locked.txt'));

if (!SCRIPT_LOCKED) {

	$config = array();
	require_once('install/classes/function.phpcore.php');
	require_once('install/classes/function.tools.php');

	$old_versions = array(
		'update' => '0.8 RC4 pl1',
		'update_rc4' => '0.8 RC4'
	);

	$packages = array(
		'install' => array(
			'title' => 'Installation',
			'description' => 'Choose this if you want to install a new copy of this software.'
		)
	);
	foreach ($old_versions as $dir => $old_version) {
		$packages[$dir] = array(
			'title' => 'Update '.$old_version.' to '.VISCACHA_VERSION,
			'description' => 'Already running Viscacha? Then choose this option to update from '.$old_version.' to the new Version!'
		);
	}

	$package = null;
	if (isset($_REQUEST['package']) && isset($packages[$_REQUEST['package']])) {
		$package = trim($_REQUEST['package']);
		$package_data = $packages[$_REQUEST['package']];
	}
	if (!empty($package)) {
		require_once('install/package/'.$package.'/steps.inc.php');
		if (isset($_REQUEST['step'])) {
			$step = intval(trim($_REQUEST['step']));
			if (!isset($steps[$step])) {
				$step = 1;
			}
		}
		else {
			$step = 1;
		}
		$nextstep = $step+1;
	}
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
<head>
	<title>Viscacha Setup</title>
	<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1" />
	<meta name="generator" content="Viscacha (http://www.viscacha.org)" />
	<link rel="stylesheet" type="text/css" href="designs/standard.css" />
	<!--[if IE]>
	<link rel="stylesheet" type="text/css" href="designs/ie.css" />
	<![endif]-->
	<link rel="up" href="javascript:self.scrollTo(0,0);" />
	<link rel="copyright" href="http://www.viscacha.org" />
</head>
<body>
<div id="container">
    <h1>&nbsp;</h1>
    <div class="breadcrumb">
    	<a href="index.php">Viscacha Setup</a> &raquo;
    	<?php if (empty($package) && !SCRIPT_LOCKED) { ?>
    	Choose Package
    	<?php } elseif (!SCRIPT_LOCKED) { ?>
    	<?php echo $package_data['title']; ?> &raquo; Step <?php echo $step; ?>
    	<?php } else { ?>
    	Locked
    	<?php } ?>
    </div>
    <div id="navigation">
    	<?php if (empty($package) && !SCRIPT_LOCKED) { ?>
		<h3>Packages</h3>
		<ul class="nav">
		<?php foreach ($packages as $id => $data) { ?>
			<li><a href="index.php?package=<?php echo $id; ?>"><?php echo $data['title']; ?></a></li>
		<?php } ?>
		</ul>
    	<?php } elseif (!SCRIPT_LOCKED) { ?>
		<h3>Steps</h3>
		<ul class="nav">
		<?php
		foreach ($steps as $id => $val) {
			echo '<li';
			if ($id == $step) {
				echo ' style="font-weight: bold;"';
			}
			echo '>';
			if ($id < $step) {
				echo '<a href="index.php?package='.$package.'&amp;step='.$id.'">'.$val.'</a>';
			}
			else {
				echo $val;
			}
			echo '</li>';
		}
		?>
		</ul>
		<?php } ?>
	</div>
	<div id="content">
		<?php if (!empty($package) && !SCRIPT_LOCKED) { ?>
		<form method="post" action="index.php?package=<?php echo $package;?>&amp;step=<?php echo $nextstep; ?>">
		<div class="border">
			<h3><?php echo $steps[$step]; ?></h3>
			<?php include(getFilePath($package, $step)); ?>
		</div>
		</form>
		<?php } elseif (!SCRIPT_LOCKED) { ?>
		<div class="border">
		<h3>Viscacha Setup</h3>
		<div class="bbody">
			What do you want to do?
			<ul>
			<?php foreach ($packages as $id => $data) { ?>
				<li>
					<strong><a href="index.php?package=<?php echo $id; ?>"><?php echo $data['title']; ?></a></strong><br />
					<span class="stext"><?php echo $data['description']; ?></span>
				</li>
			<?php } ?>
			</ul>
		</div>
		</div>
		<?php } else { ?>
		<div class="border">
			<h3>Viscacha is currently locked</h3>
			<div class="bbody">
			<p><strong>This part of Viscacha is currently locked.</strong></p>
			<p>To unlock the installation/update remove the file &quot;locked.txt&quot; in your Viscacha main folder.</p>
			</div>
		</div>
		<?php } ?>
	</div>
	<br class="invclear" />
	<div class="breadcrumb center">
		<strong><a href="http://www.viscacha.org" target="_blank">Viscacha <?php echo VISCACHA_VERSION; ?></a></strong> Copyright &copy;, MaMo Net
	</div>
</div>
</body>
</html>