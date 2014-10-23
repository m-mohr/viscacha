<?php
/*
	Viscacha - A bulletin board solution for easily managing your content
	Copyright (C) 2004-2009  The Viscacha Project

	Author: Matthias Mohr (et al.)
	Publisher: The Viscacha Project, http://www.viscacha.org
	Start Date: May 22, 2004

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

$error_handler_history = array();

// Get the bitmask for error_reporting
if ($config['error_reporting'] == '-1') {
	$bitmask = @get_cfg_var('error_reporting');
}
elseif ($config['error_reporting'] == 'E_ALL') {
	if (version_compare(PHP_VERSION, '6.0-dev', '>=')) {
		$bitmask = E_ALL ^ E_STRICT; // PHP 6 compatibility
	}
	else {
		$bitmask = E_ALL;
	}
}
elseif (defined($config['error_reporting']) == true) {
	$bitmask = constant($config['error_reporting']);
}
elseif ($config['error_reporting'] > 0) {
	$bitmask = $config['error_reporting'];
}
else {
	$bitmask = 0;
}

error_reporting($bitmask);

if ($config['error_handler'] == 1) {
	set_error_handler('msg_handler');
}
elseif ($config['error_log'] == 1) {
	set_error_handler('log_handler');
}
else {
	// Display PHP error reporting
	ini_set('display_errors', 'On');
}

/**
 * Error handler that logs only the error messages to a file and continues with the standard php error handler.
 */
function log_handler($errno, $errtext, $errfile, $errline) {
	$replevel = error_reporting();
	// If the @ error suppression operator was used, error_reporting is temporarily set to 0
	if ($replevel == 0) {
		return;
	}
	// This checks whether the error should be shown - according to what we set before with error_reporting()
	if(($errno & $replevel) != $errno) {
		return;
	}

	$errlogfile = 'data/errlog_php.inc.php';
	if (file_exists($errlogfile) == false) {
		$errlogfile = $config['fpath'].'/'.$errlogfile;
	}

	if (file_exists($errlogfile)) {
		$lines = file($errlogfile);
		foreach($lines as $key => $value) {
			$value = trim($value);
			if (empty($value)) {
				unset($lines[$key]);
			}
			else {
				$lines[$key] = $value; // Also trim it for the file
			}
		}
	}
	else {
		$lines = array();
	}

	$cols = array(
		$errno,
		makeOneLine($errtext),
		$errfile,
		$errline,
		makeOneLine($_SERVER['REQUEST_URI']),
		time(),
		PHP_VERSION." (".PHP_OS.")"
	);
	$lines[] = implode("\t", $cols);

	@file_put_contents($errlogfile, implode("\n", $lines));

	return false; // Return to php error handler
}

/**
* Custom Error handler, call with trigger_error if required.
*/
function msg_handler($errno, $errtext, $errfile, $errline) {
	$replevel = error_reporting();
	// If the @ error suppression operator was used, error_reporting is temporarily set to 0
	if ($replevel == 0) {
		return;
	}
	// This checks whether the error should be shown - according to what we set before with error_reporting()
	if(($errno & $replevel) != $errno) {
		return;
	}

	global $db, $config, $error_handler_history;

	$errdate = date("Y-m-d H:i:s (T)");

	$errortype = array (
		// Not handled: E_ERROR, E_CORE_ERROR, E_CORE_WARNING, E_COMPILE_ERROR, E_COMPILE_WARNING, E_PARSE
		E_RECOVERABLE_ERROR	=> "PHP Error",
		E_WARNING			=> "PHP Warning",
		E_NOTICE			=> "PHP Notice",
		E_DEPRECATED		=> "PHP Deprecated",
		E_STRICT			=> "PHP Strict",
		E_USER_ERROR		=> "Viscacha Error",
		E_USER_WARNING		=> "Viscacha Warning",
		E_USER_NOTICE		=> "Viscacha Notice",
		E_USER_DEPRECATED 	=> "Viscacha Deprecated"
	);

	log_handler($errno, $errtext, $errfile, $errline);

	switch ($errno) {
		case E_WARNING:
		case E_NOTICE:
		case E_USER_WARNING:
		case E_USER_NOTICE:
			echo "<br /><strong>{$errortype[$errno]}</strong>: {$errtext} (File: <tt>{$errfile}</tt> on line <tt>{$errline}</tt>)";
			$error_handler_history[] = compact('errno', 'errtext', 'errfile', 'errline');
			return true; // Avoid PHP error handler
		break;
		case E_USER_ERROR:
		case E_RECOVERABLE_ERROR:
			if (isset($db) && is_a($db, 'DB_Driver')) {
				$db->close();
			}
			while (ob_get_length() !== false) {
				ob_end_clean();
			}
			?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
		<title>Viscacha <?php echo $config['version']; ?> &raquo; Error</title>
		<style type="text/css">
		<!--
		body { color: #000; background-color: #fafafa; font-size: 12px; line-height: 130%; font-family: sans-serif; margin: auto 10%; border: 1px solid #aaa; }
		p { margin: 0; padding: 2px 5px 2px 15px; }
		a { color: #a80000; }
		a:hover { color: #000; }
		h1 { text-align: center; padding: 10px; margin: 0 0 20px 0; background-color: #eee; border-bottom: 1px solid #aaaaaa; }
		h3 { padding: 0 5px; margin: 20px 0 7px 0; color: #a80000; border-bottom: 1px solid #eee; }
		.code { background: #fff; border: 1px solid #ddd; margin: 2px 5px 2px 15px; padding: 2px; font-family: monospace; list-style: none; }
		.lineone { padding: 0 5px; margin: 2px 0; background:#f9f9f9; }
		.linetwo { padding: 0 5px; margin: 2px 0; background: #fcfcfc; }
		.mark { padding: 0 5px; margin: 2px 0; background: #eedddd; color: #880000; font-weight: bold; }
		.center { text-align: center; }
		-->
		</style>
	</head>
	<body>
		<h1><?php echo $errortype[$errno]; ?></h1>
		<p class="center">
			[<a href="<?php echo $config['furl']; ?>/index.php">Return to Index</a>]
			<?php if (check_hp($_SERVER['HTTP_REFERER'])) { ?>
			&nbsp;&nbsp;[<a href="<?php echo htmlspecialchars($_SERVER['HTTP_REFERER']); ?>">Return to last Page</a>]
			<?php } ?>
		</p>
		<h3>Error Message</h3>
		<p><?php echo $errtext; ?></p>
		<h3>Error Details</h3>
		<p>
			<strong>File:</strong> <?php echo $errfile; ?><br />
			<strong>Line:</strong> <?php echo $errline; ?><br />
			<strong>Date:</strong> <?php echo $errdate; ?>
		</p>
		<h3>Code Snippet</h3>
		<?php echo getErrorCodeSnippet($errfile, $errline); ?>
		<h3>Backtrace</h3>
		<?php echo get_backtrace(2); ?>
		<?php if (count($error_handler_history) > 0) { ?>
		<h3>Previous Notices and Warnings</h3>
		<p>Additionally <?php echo count($error_handler_history); ?> notices and/or warnings occured:</p>
		<ul>
		<?php foreach ($error_handler_history as $e) { ?>
			<li>
				<strong><?php echo $errortype[$e['errno']]; ?></strong>: <?php echo $e['errtext']; ?><br />
				File: <tt><?php echo $e['errfile']; ?></tt> - Line: <tt><?php echo $e['errline']; ?></tt>
			</li>
		<?php } ?>
		</ul>
		<?php } ?>
		<h3>Contact</h3>
		<p>Please notify the board administrator: <a href="mailto:<?php echo $config['forenmail']; ?>"><?php echo $config['forenmail']; ?></a></p>
		<h3>Copyright</h3>
		<p>
			Powered by <strong><a href="http://www.viscacha.org" target="_blank">Viscacha <?php echo $config['version']; ?></a></strong><br />
			Copyright &copy; 2004-2009, The Viscacha Project
		</p>
	</body>
</html>
			<?php
			exit;
		break;
		default: // E_STRICT
			return; // Do nothing
		break;
	}
}

function get_backtrace($skip) {
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
		if ($number < $skip) {
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

function makeOneLine($str) {
	return str_replace(array("\r\n","\n","\r","\t","\0"), ' ', $str);
}
?>