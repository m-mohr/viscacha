<?php
error_reporting(E_ALL);

chdir('../');

define('VISCACHA_VERSION', '0.8.1.2');
define('VISCACHA_CORE', '1');
define('SCRIPTNAME', 'install');
define('SCRIPT_LOCKED', file_exists('./locked.txt'));

if (!SCRIPT_LOCKED) {

	$config = array();
	require_once('install/classes/function.phpcore.php');
	require_once('install/classes/function.tools.php');

	$old_versions = array();
	if (file_exists("install/package/update/steps.inc.php")) {
		$old_versions['update'] = '0.8.1.1';
	}

	$packages = array();
	if (file_exists("install/package/install/steps.inc.php")) {
		$packages['install'] = array(
			'title' => 'Installation',
			'description' => 'Choose this if you want to install a new copy of this software.'
		);
	}
	else {
		$packages[] = array(
			'title' => 'Installation',
			'description' => 'For a fresh installation you need to download the Install-Package for Viscacha '.VISCACHA_VERSION.'!'
		);
	}
	if (count($old_versions) > 0) {
		foreach ($old_versions as $dir => $old_version) {
			$packages[$dir] = array(
				'title' => 'Update '.$old_version.' to '.VISCACHA_VERSION,
				'description' => 'Already running Viscacha? Choose this option to update from '.$old_version.' to the new version!'
			);
		}
	}
	else {
		$packages[] = array(
			'title' => 'Update',
			'description' => 'For an update you need to download the Update-Package!'
		);
	}

	$package = null;
	if (isset($_REQUEST['package']) && isset($packages[$_REQUEST['package']])) {
		$package = GPC_escape($_REQUEST['package'], GPC_ALNUM);
		$package_data = $packages[$_REQUEST['package']];
	}
	if (!empty($package)) {
		require_once('install/package/'.$package.'/steps.inc.php');
		if (isset($_REQUEST['step'])) {
			$step = intval($_REQUEST['step']);
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
			<?php if(!is_numeric($id)) { ?>
			<li><a href="index.php?package=<?php echo $id; ?>"><?php echo $data['title']; ?></a></li>
			<?php } ?>
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
		<?php if (version_compare(PHP_VERSION, '5.0.0', '<') && empty($_REQUEST['skip_php'])) { ?>
		<div class="border">
			<h3>Error: PHP Version mismatch</h3>
			<div class="bbody">
			Support for PHP 4 has been discontinued since Viscacha 0.8 RC7.<br />
			Please consider upgrading to (the latest version of) PHP 5 or you won't be able to use Viscacha.
			</div>
			<div class="bfoot center"><a class="submit" href="index.php?skip_php=1">Continue anyway</a></div>
		</div>
		<?php } else { ?>
		<div class="border">
		<h3>Viscacha Setup</h3>
		<div class="bbody">
			What do you want to do?
			<ul>
			<?php foreach ($packages as $id => $data) { ?>
				<li>
					<strong>
					<?php if(is_numeric($id)) { echo $data['title']; } else { ?>
					<a href="index.php?package=<?php echo $id; ?>"><?php echo $data['title']; ?></a>
					<?php } ?>
					</strong><br />
					<span class="stext"><?php echo $data['description']; ?></span>
				</li>
			<?php } ?>
			</ul>
		</div>
		</div>
		<?php } } else { ?>
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
		Powered by <strong><a href="http://www.viscacha.org" target="_blank">Viscacha <?php echo VISCACHA_VERSION; ?></a></strong> &middot; Copyright &copy; 2004-2009, The Viscacha Project
	</div>
</div>
</body>
</html>