<?php

namespace Viscacha\System;

class PhpSys {

	public static function getClassNameFromFile($file, $includeNamespace = true) {
		$content = file_get_contents($file);
		$tokens = token_get_all($content);
		$tokenCount = count($tokens);
		$class = null;
		$namespace = '';
		for ($i = 0; $i < $tokenCount; $i++) {
			if ($includeNamespace) {
				if ($tokens[$i][0] === T_NAMESPACE) {
					for ($j = $i + 1; $j < $tokenCount; $j++) {
						if ($tokens[$j][0] === T_STRING) {
							$namespace .= '\\' . $tokens[$j][1];
						} else if ($tokens[$j] === '{' || $tokens[$j] === ';') {
							break;
						}
					}
				}
			}

			if ($tokens[$i][0] === T_CLASS) {
				for ($j = $i + 1; $j < $tokenCount; $j++) {
					if ($tokens[$j] === '{') {
						$class = $tokens[$i + 2][1];
						break;
					}
				}
			}
		}

		if (!$class) {
			return null;
		} else if ($includeNamespace) {
			return $namespace . '\\' . $class;
		} else {
			return $class;
		}
	}

	public static function isHttps() {
		if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') {
			return true;
		} else if (isset($_SERVER['HTTPS']) && self::isIniValueTrue($_SERVER['HTTPS'])) {
			return true;
		} else {
			return false;
		}
	}

	private static function isIniValueTrue() {
		return ($value === true || $value === 1 || $value === 'true' || $value === '1' || \Str::lower($value) === 'on');
	}

	public static function getMaxUploadSize() {
		$keys = array(
			'post_max_size' => 0,
			'upload_max_filesize' => 0
		);
		foreach ($keys as $key => $bytes) {
			$keys[$key] = self::fromIniAsSize($key);
		}
		return min($keys);
	}

	public static function fromIniAsSize($name) {
		$value = ini_get($name);
		$size = trim($value);
		$last = \Str::lower(substr($size, -1));
		$size = intval($size);

		switch ($last) {
			case 'g':
				$size *= 1024;
			case 'm':
				$size *= 1024;
			case 'k':
				$size *= 1024;
		}
		return $size;
	}

	public static function isWindows() {
		if (function_exists('php_uname') && \Str::contains(@php_uname(), 'windows', false)) {
			return true;
		} else if (isset($_SERVER['OS']) && \Str::contains($_SERVER['OS'], 'Windows', false)) {
			return true;
		} else if (defined(PHP_OS) && \Str::upper(\Str::substr(PHP_OS, 0, 3)) == 'WIN') {
			return true;
		} else {
			return false;
		}
	}

	public static function isMac() {
		$mac = \Str::upper(\Str::substr(PHP_OS, 0, 3));
		return ($mac == 'MAC' || $mac == 'DAR');
	}

}
