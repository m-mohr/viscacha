<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

/* Error Handling */
if (!empty($config['error_reporting'])) {
	$bitmask = 0;
	if (is_numeric($config['error_reporting']) == false && $config['error_reporting'] > 0) {
		$bitmask = $config['error_reporting'];
	}
	else if (defined($config['error_reporting']) == true) {
		$bistmask = constant($config['error_reporting']);
	}
	error_reporting($bitmask);
}
if (isset($config['error_handler']) && $config['error_handler'] == 1) {
	set_error_handler('msg_handler');
}

/**
* Error and message handler, call with trigger_error if required
*/
function msg_handler($errno, $errtext, $errfile, $errline) {
	// If the @ error suppression operator was used, error_reporting is temporarily set to 0
	if (error_reporting() == 0) {
		return;
	}

	global $db, $config;

	$errdate = date("Y-m-d H:i:s (T)");

	$errortype = array (
		E_ERROR			=> "PHP Error",
		E_WARNING		=> "PHP Warning",
		E_NOTICE		=> "PHP Notice",
		E_USER_ERROR	=> "User Error",
		E_USER_WARNING	=> "User Warning",
		E_USER_NOTICE	=> "User Notice"
	);

	if ($config['error_log'] == 1) {
		switch ($errno) {
			case E_WARNING:
			case E_NOTICE:
			case E_USER_WARNING:
			case E_USER_NOTICE:
			case E_USER_ERROR:
				$errlogfile = 'data/errlog_php.inc.php';
				if (file_exists($errlogfile) == false) {
					$errlogfile = $config['fpath'].'/data/errlog_php.inc.php';
				}
				$new = array();
				if (file_exists($errlogfile)) {
					$lines = file($errlogfile);
					foreach ($lines as $row) {
						$row = trim($row);
						if (!empty($row)) {
							$new[] = $row;
						}
					}
				}
				else {
					$new = array();
				}
				$errtext2 = str_replace(array("\r\n","\n","\r","\t"), " ", $errtext);
				$sru = str_replace(array("\r\n","\n","\r","\t"), " ", $_SERVER['REQUEST_URI']);
				$new[] = $errno."\t".$errtext2."\t".$errfile."\t".$errline."\t".$sru."\t".time()."\t".PHP_VERSION." (".PHP_OS.")";
				@file_put_contents($errlogfile, implode("\n", $new));
			break;
		}
	}

	switch ($errno) {
		case E_WARNING:
		case E_NOTICE:
		case E_USER_WARNING:
		case E_USER_NOTICE:
			echo "<br /><strong>".$errortype[$errno]."</strong>: ".$errtext." (File: <tt>".$errfile."</tt> on line <tt>".$errline."</tt>)";
		break;
		case E_USER_ERROR:
			if (isset($db) && is_a($db, 'DB_Driver')) {
				$db->close();
			}
			if (viscacha_function_exists('ob_clean')) {
				@ob_clean();
			}
			?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
		<title>Viscacha <?php echo $config['version']; ?> &raquo; Error</title>
		<style type="text/css">
		<!--
		body{
			color: #000000;
			background-color: #FAFAFA;
			font-size: 12px;
			line-height: 130%;
			font-family: Sans-Serif;
			margin-left: 10%;
			margin-right: 10%;
			border: 1px solid #aaaaaa;
		}
		p {
			margin: 0px;
			padding: 2px;
			padding-left: 15px;
			padding-right: 5px;
		}
		a {
			color: #A80000;
		}
		a:hover {
			color: #000000;
		}
		h1 {
			text-align: center;
			padding: 10px;
			margin: 0px;
			margin-bottom: 20px;
			background-color: #eeeeee;
			border-bottom: 1px solid #aaaaaa;
		}
		h3 {
			padding: 0px;
			margin: 0px;
			padding-left: 5px;
			padding-right: 5px;
			margin-bottom: 7px;
			margin-top: 20px;
			color: #A80000;
			border-bottom: 1px solid #EEE;
		}
		.code {
			background: #FFFFFF;
			border: 1px solid #dddddd;
			margin-right: 5px;
			margin-bottom: 2px;
			margin-top: 2px;
			margin-left: 15px;
			padding: 2px;
			font-family: Monospace;
			list-style: none;
		}
		.lineone {
			padding:0 5px;
			margin:2px 0;
			background:#F9F9F9;
		}
		.center {
			text-align: center;
		}
		.linetwo {
			padding:0 5px;
			margin:2px 0;
			background:#FCFCFC;
		}
		.mark {
			padding:0 5px;
			margin:2px 0;
			background: #eedddd;
			color: #880000;
			font-weight: bold;
		}
		-->
		</style>
	</head>
	<body>
		<h1>General Error</h1>
		<p class="center">
			[<a href="<?php echo $config['furl']; ?>/index.php">Return to Index</a>]
			<?php if (check_hp($_SERVER['HTTP_REFERER'])) { ?>
			&nbsp;&nbsp;[<a href="<?php echo htmlspecialchars($_SERVER['HTTP_REFERER']); ?>">Return to last Page</a>]
			<?php } ?>
		</p>
		<h3>Error Message</h3>
		<p><strong><?php echo $errortype[$errno]; ?></strong>: <?php echo $errtext; ?></p>
		<h3>Error Details</h3>
		<p>
			File: <?php echo $errfile; ?><br />
			Line: <?php echo $errline; ?><br />
			Date: <?php echo $errdate; ?><br />
		</p>
		<h3>Code Snippet</h3>
		<?php echo getErrorCodeSnippet($errfile, $errline); ?>
		<h3>Backtrace</h3>
		<?php echo get_backtrace(); ?>
		<h3>Contact</h3>
		<p>Please notify the board administrator: <a href="mailto:<?php echo $config['forenmail']; ?>"><?php echo $config['forenmail']; ?></a></p>
		<h3>Copyright</h3>
		<p>
			<strong><a href="http://www.viscacha.org" target="_blank">Viscacha <?php echo $config['version']; ?></a></strong><br />
			Copyright &copy; by MaMo Net
		</p>
	</body>
</html>
			<?php
			exit;
		break;
	}
}

function get_backtrace() {
	global $config;

	if (viscacha_function_exists('debug_backtrace')) {
		$backtrace = debug_backtrace();
	}
	else {
		$output = '<p>Backtrace is not available!</p>';
		return $output;
	}
	$path = realpath($config['fpath']);

	$output = '';
	foreach ($backtrace as $number => $trace) {
		// We skip the first one, because it only shows this file/function
		if ($number == 0) {
			continue;
		}

		if (isset($trace['file'])) {
			// Strip the current directory from path
			$trace['file'] = str_replace(array($path, '\\'), array('', '/'), $trace['file']);
			$trace['file'] = substr($trace['file'], 1);
		}

		$args = array();
		if (isset($trace['args']) && is_array($trace['args'])) {
			foreach ($trace['args'] as $argument) {
				switch (gettype($argument)) {
					case 'integer':
					case 'double':
						$args[] = $argument;
					break;

					case 'string':
						$argument = htmlspecialchars(substr($argument, 0, 64)) . ((strlen($argument) > 64) ? '...' : '');
						$args[] = "'{$argument}'";
					break;

					case 'array':
						$args[] = 'Array(' . count($argument) . ')';
					break;

					case 'object':
						$args[] = 'Object(' . get_class($argument) . ')';
					break;

					case 'resource':
						$args[] = 'Resource(' . strstr($a, '#') . ')';
					break;

					case 'boolean':
						$args[] = ($argument) ? 'true' : 'false';
					break;

					case 'NULL':
						$args[] = 'NULL';
					break;

					default:
						$args[] = 'Unknown';
				}
			}
		}

		$trace['file'] = (!isset($trace['file'])) ? 'N/A' : $trace['file'];
		$trace['line'] = (!isset($trace['line'])) ? 'N/A' : $trace['line'];
		$trace['class'] = (!isset($trace['class'])) ? '' : $trace['class'];
		$trace['type'] = (!isset($trace['type'])) ? '' : $trace['type'];

		$output .= '<ul class="code">';
		$output .= '<li class="linetwo"><b>File:</b> ' . htmlspecialchars($trace['file']) . '</li>';
		$output .= '<li class="lineone"><b>Line:</b> ' . $trace['line'] . '</li>';
		$output .= '<li class="linetwo"><b>Call:</b> ' . htmlspecialchars($trace['class'] . $trace['type'] . $trace['function']) . '(' . ((count($args)) ? implode(', ', $args) : '') . ')</li>';
		$output .= '</ul>';
	}
	return $output;
}


function getErrorCodeSnippet($file, $line) {
    $lines = file_exists($file) ? file($file) : null;
    if(!is_array($lines)) {
        return 'Could not load code snippet!';
    }

	$code    = '<ul class="code">';
	$total   = count($lines);

	for($i = $line - 5; $i <= $line + 5; $i++) {
		if(($i >= 1) && ($i <= $total)) {
            $codeline = @rtrim(htmlentities($lines[$i - 1]));
            $codeline = str_replace("\t", '&nbsp;&nbsp;&nbsp;&nbsp;', $codeline);
            $codeline = str_replace(' ',  '&nbsp;',                   $codeline);

            $i = sprintf("%05d", $i);

            $class = $i % 2 == 0 ? 'lineone' : 'linetwo';

            if($i != $line) {
                $code .= "<li class=\"{$class}\"><span>{$i}</span> {$codeline}</li>\n";
            }
            else {
                $code .= "<li class=\"mark\"><span>{$i}</span> {$codeline}</li>\n";
            }
        }
	}

    $code .= "</ul>";

	return $code;
}
?>