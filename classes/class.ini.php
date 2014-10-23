<?php
/*
* Scripts are taken from: http://www.php.net/manual/function.parse-ini-file.php
*/

class INI {

var $commentchar;

/**
* Constructor for this class
*
* This function only constructs this class and initialzes some variables.
* <b>Instructions:</b>
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
* Searches in an ini-file for a parameter
*
* Searches in an ini-file for a parameter and returns the section if wanted.
*
* @param	string	Filename
* @param	string	Search parameter
* @param	boolean	Return the section, where the parameter is found
*
* @author	dawalama at gmail dot com
*/
function search ( $filename, $search_param, $return_section = false ) {
	$search_key =  (isset($search_param['key']) ? $search_param['key'] : false);
	$search_value = (isset($search_param['value']) ? $search_param['value'] : false);
	if ( !($search_key !==false || $search_value !==false) ){
		return false;
	}
	$retvalue = false;
	$handle = fopen($filename, 'r');
	if ( ($search_key !== false) && ($search_value !== false) ){
		$key_found = false;
		$retvalue['key'] = false;
		$retvalue['value'] = false;
		while( !feof($handle) ) {
  			$line = trim(fgets($handle, 4096));
			if (preg_match("/^\[$search_key\].*?$/s",$line)) {
				$key_found = true;
				$retvalue['key'] = true;
				continue;
			}
			if ($key_found){
				if (preg_match("/^\[.*?$/", trim($line))) {
					break;
				}
				else{
					if ($return_section){
						if ($line != '') {
							list($k, $v) = split("=", $line);
							$retvalue[$search_key][$k] = preg_replace("/".$this->commentchar.".*$/", "", $v);
						}
					}
				}
				if (preg_match("/^$search_value\s*?=.*$/", $line)) {
					$retvalue['value'] = true;
					break;
				}
			}
		}
 	}
 	elseif ($search_key !== false) {
		$keyfound = false;
		while ( !feof($handle) ) {
			$line = trim(fgets($handle, 4096));
			if (preg_match("/^\[$search_key\].*?$/s",$line)) {
				$retvalue  = true;
				if (!$return_section) {
					break;
				}
				else{
					$retvalue = Array();
					$keyfound = true;
					continue;
				}
			}
			if ( $keyfound ) {
				if (preg_match("/^\[.*?$/", trim($line))){
					break;
				}
				else{
					if ($return_section) {
						if ($line != '') {
							list($k, $v) = split("=", $line);
							$retvalue[$search_key][$k] = preg_replace("/".$this->commentchar.".*$/", "", $v);
						}
					}
				}
			}
		}
	}
	elseif ($search_value !== false){
		while ( !feof($handle) ){
			$line = trim(fgets($handle, 4096));
			if (preg_match("/^$search_value\s*?=.*$/", $line)){
				$retvalue = true;
				if ($return_section){
					$retvalue = array();
					if ($line != ''){
						list($k, $v) = split("=", $line);
						$retvalue[$k] = preg_replace("/".$this->commentchar.".*$/", "", $v);
					}
				}
				break;
			}
		}
	}
	fclose($handle);
	return $retvalue;
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
	$array1 = file($filename);
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
		else{
			//It's a comment or blank line.  Ignore.
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
	$handle = fopen($filename, 'wb');
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
		fwrite ($handle, $comtext."\r\n");
	}
	foreach ($array1 as $sections => $items) {
		//Write the section
		if (isset($section)) {
			fwrite ($handle, "\r\n");
		}
		//$section = ucfirst(preg_replace('/[\0-\37]|[\177-\377]/', "-", $sections));
		if (is_array($items)) {
			$section = preg_replace('/[\0-\37]|\177/', "-", $sections);
			fwrite ($handle, "[".$section."]\r\n");
			foreach ($items as $keys => $values) {
				//Write the key/value pairs
				//$key = ucfirst(preg_replace('/[\0-\37]|=|[\177-\377]/', "-", $keys));
				$key = preg_replace('/[\0-\37]|=|\177/', "-", $keys);
	  			if (substr($key, 0, 1)==$this->commentchar) { 
	  				$key = '-'.substr($key, 1);
	  			}
	  			$value = addcslashes($values,'');
	  			fwrite ($handle, ' '.$key.' = "'.$value."\"\r\n");
			}
		}
		else {
			$key = preg_replace('/[\0-\37]|=|\177/', "-", $sections);
	  		if (substr($key, 0, 1)==$this->commentchar) { 
	  			$key = '-'.substr($key, 1);
	  		}
	  		$value = addcslashes($items,'');
	  		fwrite ($handle, $key.' = "'.$value."\"\r\n");
		}
  	}
	fclose($handle);
}

}
?>