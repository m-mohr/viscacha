<?php 
define('VISCACHA_VERSION', '0.8 Beta 1');
error_reporting(E_ERROR);

if (!isset($_REQUEST) || !is_array($_REQUEST)) {
	$_REQUEST = array_merge($_GET, $_POST, $_COOKIE);
}

$config = array();
require_once('../classes/function.phpcore.php');
require_once('lib/function.variables.php');

$steps = array(
1 => 'Initialize Setup Process',
2 => 'License Agreement',
3 => 'FTP Settings',
4 => 'Prepare File System',
5 => 'Basic Settings',
6 => 'Database Settings',
7 => 'Create Tables',
8 => 'Create Administrator Account',
9 => 'Complete Installation'
);
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
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
<head>
	<title>Viscacha Setup</title>
	<meta http-equiv="content-type" content="text/html; charset=ISO-8859-1" />
	<meta name="generator" content="Viscacha (http://www.mamo-net.de)" />
	<link rel="stylesheet" type="text/css" href="designs/standard.css" />
	<!--[if IE]>
	<link rel="stylesheet" type="text/css" href="designs/ie.css" />
	<![endif]-->
	<link rel="up" href="javascript:self.scrollTo(0,0);" />
	<link rel="copyright" href="http://www.mamo-net.de" />
</head>
<body>
<div id="container">
    <h1>&nbsp;</h1>
    <div class="breadcrumb"><a href="index.php">Viscacha Setup</a> &raquo; Step <?php echo $step; ?></div>
    <div id="navigation">
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
				echo '<a href="index.php?step='.$id.'">'.$val.'</a>';
			}
			else {
				echo $val;
			}
			echo '</li>';
		}
		?>
		</ul>
	</div>
	<div id="content">
		<form method="post" action="index.php?step=<?php echo $nextstep; ?>">
		<div class="border">
			<h3><?php echo $steps[$step]; ?></h3>
			<?php include('steps/'.$step.'.php'); ?>
		</div>
		</form>
	</div>
	<br class="invclear" />
	<div class="breadcrumb center">
		<strong><a href="http://www.mamo-net.de" target="_blank">Viscacha <?php echo VISCACHA_VERSION; ?></a></strong> Copyright &copy;, MaMo Net
	</div>
</div>
</body>
</html>
