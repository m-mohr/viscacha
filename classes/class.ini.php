<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

/*
* Scripts are taken from: http://www.php.net/manual/function.parse-ini-file.php
*/

class INI {

	var $commentchar;

	/**
	* This function only constructs this class and initialzes some variables.
	*
	* Instructions:
	* Sections can use any character excluding ASCII control characters and ASCII
	* DEL.  (You may even use [ and ] characters as literals!)
	* Keys can use any character excluding ASCII control characters, ASCII DEL,
	* ASCII equals sign (=), and not start with the user-defined comment
	* character.
	* Values are binary safe (encoded with C-style backslash escape codes) and may
	* be enclosed by double-quotes (to retain leading & trailing spaces).
	* User-defined comment character can be any non-white-space ASCII character
	* excluding ASCII opening bracket ([).
	*
	* @param	string	Charakter to use for Comments
	*/
	function INI ($commentchar = ';') {
		$this->commentchar = $commentchar;
	}

	/**
	* This function reads and parses an ini-file.
	*
	* This function is case-insensitive when reading sections and keys,
	* returning an array with lower-case keys.
	*
	* @param	string	Filename
	*
	* @author	Copyright (C) 2005 Justin Frim <phpcoder@cyberpimp.pimpdomain.com>
	*/
	function read ($filename) {
		$array1 = @file($filename);
		if (!is_array($array1)) {
			trigger_error("Could not read ini-file {$filename}", E_USER_WARNING);
			return array();
		}
		return $this->_convert($array1);
	}

	/**
	* This function reads and parses an ini-string.
	*
	* This function is case-insensitive when reading sections and keys,
	* returning an array with lower-case keys.
	*
	* @param	string	String
	*/
	function parse ($str) {
		$array1 = preg_split("(\r\n|\r|\n)", $str);
		return $this->_convert($array1);
	}

	/**
	* This function reads and parses an ini-array.
	*
	* This function is case-insensitive when reading sections and keys,
	* returning an array with lower-case keys.
	*
	* @param	string	Array
	*/
	function _convert($array1) {
		global $gpc;
		$array2 = array();
		$section = '';
		foreach ($array1 as $filedata) {
			$dataline = trim($filedata);
			$firstchar = substr($dataline, 0, 1);
			if ($firstchar != $this->commentchar && $dataline != '') {
				//It's an entry (not a comment and not a blank line)
				if ($firstchar == '[' && substr($dataline, -1, 1) == ']') {
					//It's a section
					$section = strtolower(substr($dataline, 1, -1));
				}
				else{
					//It's a key...
					$delimiter = strpos($dataline, '=');
					if ($delimiter > 0) {
						//...with a value
						$key = strtolower(trim(substr($dataline, 0, $delimiter)));
						$value = trim(substr($dataline, $delimiter + 1));
						if (substr($value, 0, 1) == '"' && substr($value, -1, 1) == '"') {
							$value = substr($value, 1, -1);
							$value = str_replace('\\r', "\r", $value);
							$value = str_replace('\\n', "\n", $value);
						}
						if (empty($section)) {
							$array2[$key] = stripcslashes($value);
						}
						else {
							$array2[$section][$key] = stripcslashes($value);
						}
					}
					else{
						//...without a value
						if (empty($section)) {
							$array2[strtolower(trim($dataline))]='';
						}
						else {
							$array2[$section][strtolower(trim($dataline))]='';
						}

					}
				}
			}
		}
		return $array2;
	}

	/**
	* This function writes an ini-file.
	*
	* This function writes sections and keys with first character capitalization.
	* Invalid characters are converted to ASCII dash / hyphen (-).  Values are
	* always enclosed by double-quotes.
	* This function also provides a method to automatically prepend a comment
	* header from ASCII text with line breaks, regardless of whether CRLF, LFCR,
	* CR, or just LF line break sequences are used!  (All line breaks are
	* translated to CRLF)
	*
	* @param	string	Filename
	* @param	array	Array to write
	* @param	string	Comment (Optional)
	*
	* @author	Copyright (C) 2005 Justin Frim <phpcoder@cyberpimp.pimpdomain.com>
	*/
	function write ($filename, $array1, $commenttext = '') {
		global $filesystem;
		$data = '';
		if ($commenttext!='') {
			$comtext = $this->commentchar.
				str_replace($this->commentchar, "\r\n".$this->commentchar,
					str_replace ("\r", $this->commentchar,
						str_replace("\n", $this->commentchar,
							str_replace("\n\r", $this->commentchar,
								str_replace("\r\n", $this->commentchar, $commenttext)
							)
						)
					)
				)
			;
			if (substr($comtext, -1, 1)==$this->commentchar && substr($comtext, -1, 1)!=$this->commentchar) {
				$comtext = substr($comtext, 0, -1);
			}
			$data .= $comtext."\r\n";
		}
		foreach ($array1 as $sections => $items) {
			//Write the section
			if (isset($section)) {
				$data .= "\r\n";
			}
			//$section = ucfirst(preg_replace('/[\0-\37]|[\177-\377]/', "-", $sections));
			if (is_array($items)) {
				$section = preg_replace('/[\0-\37]|\177/', "-", $sections);
				$data .= "[".$section."]\r\n";
				foreach ($items as $keys => $values) {
					//Write the key/value pairs
					//$key = ucfirst(preg_replace('/[\0-\37]|=|[\177-\377]/', "-", $keys));
					$key = preg_replace('/[\0-\37]|=|\177/', "-", $keys);
		  			if (substr($key, 0, 1)==$this->commentchar) {
		  				$key = '-'.substr($key, 1);
		  			}
			  		$values = str_replace("\r", '\r', $values);
			  		$values = str_replace("\n", '\n', $values);
		  			$value = addcslashes($values,'');
		  			$data .= ' '.$key.' = "'.$value."\"\r\n";
				}
			}
			else {
				$key = preg_replace('/[\0-\37]|=|\177/', "-", $sections);
		  		if (substr($key, 0, 1) == $this->commentchar) {
		  			$key = '-'.substr($key, 1);
		  		}
		  		$items = str_replace("\r", '\r', $items);
		  		$items = str_replace("\n", '\n', $items);
		  		$value = addcslashes($items,'');
		  		$data .= $key.' = "'.$value."\"\r\n";
			}
	  	}
	  	$filesystem->chmod($filename, 0666);
		$filesystem->file_put_contents($filename, $data);
	}

}
?>