<?php
/**
 * 
 * class uploader
 * 
 * Copyright 1999, 2002, 2003 David Fox, Dave Tufts
 * 
 * Usage, setup, and license at the bottom of this page (README)
 * 
 * @version:      2.15
 * @last_update:  2004-02-18
 * @description:  PHP file upload class
 * 
 * 
 *	METHODS:
 *		uploader()	 		- constructor
 *		max_filesize() 		- set a max filesize in bytes
 *		max_image_size() 	- set max pixel dimenstions for image uploads
 *		upload() 			- checks if file is acceptable, uploads file to server's temp directory
 *		save_file() 		- moves the uploaded file and renames it depending on the save_file($overwrite_mode)
 *		cleanup_text_file()	- (PRIVATE) convert Mac and/or PC line breaks to UNIX
 *		get_error() 		- (PRIVATE) gets language-specific error message
 *
 */
class uploader {

	var $file;
	var $path;
	var $language;
	var $acceptable_file_types;
	var $error;
	var $errors; // Depreciated (only for backward compatability)
	var $accepted;
	var $max_filesize;
	var $max_image_width;
	var $max_image_height;


	/**
	 * object uploader ();
	 * Class constructor
	 * @examples:			$f = new uploader();
	 */
	function uploader () {
		$this->error   = '';
	}
	
	
	/**
	 * void max_filesize ( int size);
	 * 
	 * Set the maximum file size in bytes ($size), allowable by the object.
	 * NOTE: PHP's configuration file also can control the maximum upload size, which is set to 2 or 4 
	 * megs by default. To upload larger files, you'll have to change the php.ini file first.
	 * 
	 * @param size 			(int) file size in bytes
	 * 
	 */
	function max_filesize($size){
		$this->max_filesize = (int) $size;
	}


	/**
	 * void max_image_size ( int width, int height );
	 * 
	 * Sets the maximum pixel dimensions. Will only be checked if the 
	 * uploaded file is an image
	 * 
	 * @param width			(int) maximum pixel width of image uploads
	 * @param height		(int) maximum pixel height of image uploads
	 * 
	 */
	function max_image_size($width, $height){
		$this->max_image_width  = (int) $width;
		$this->max_image_height = (int) $height;
	}
	
	
	/**
	 * rename_file ( dir, file, newname);
	 * 
	 * Renames a file in an Upload Directory.
	 * If the file already exists, the programm tries to delete it.
	 * If there is an error in progress, the programm makes nothing!
	 * The extension of $file will be added to $newname!
	 * 
	 */
	function rename_file($dir, $file, $newname){
	
	if (file_exists($dir.$file)) {
	    
		$this->file["extention"] = get_extension($dir.$file);
	    
	    if (file_exists($dir.$newname.$this->file["extention"])) {
	        unlink($dir.$newname.$this->file["extention"]);
	    }
	    
	    rename($dir.$file,$dir.$newname.$this->file["extention"]);
	    
	    return($this->file["extention"]);
	}
	
	}
	
	/**
	 * bool upload (string filename[, string accept_type[, string extension]]);
	 * 
	 * Checks if the file is acceptable and uploads it to PHP's default upload diretory
	 *
	 * 
	 */
	function upload($filename='', $accept_type=array() ) {
		
		if (is_array($accept_type) && count($accept_type)) {
		    $this->acceptable_file_types = $accept_type;
		}
		else {
		    $this->acceptable_file_types = array();
		}
		
		if (!isset($_FILES) || !is_array($_FILES[$filename]) || !$_FILES[$filename]['name']) {
			$this->error = $this->get_error(0);
			$this->accepted  = FALSE;
			return FALSE;
		}
				
		// Copy PHP's global $_FILES array to a local array
		$this->file = $_FILES[$filename];
		$this->file['file'] = $filename;
		
		// Initialize empty array elements
		if (!isset($this->file['extention'])) $this->file['extention'] = "";
		if (!isset($this->file['size']))      $this->file['size']      = "";
		if (!isset($this->file['width']))     $this->file['width']     = "";
		if (!isset($this->file['height']))    $this->file['height']    = "";
		if (!isset($this->file['tmp_name']))  $this->file['tmp_name']  = "";
		if (!isset($this->file['raw_name']))  $this->file['raw_name']  = "";
		
		// test max size
		if($this->max_filesize && ($this->file["size"] > $this->max_filesize)) {
			$this->error = $this->get_error(1);
			$this->accepted  = FALSE;
			return FALSE;
		}
		$this->file["extention"] = get_extension($this->file['name']);
		if($this->file["extention"] == '.gif' or $this->file["extention"] == '.png' or $this->file["extention"] == '.jpg' or $this->file["extention"] == '.jpeg' or $this->file["extention"] == '.jpe' or $this->file["extention"] == '.swf') {
			
			/* IMAGES (gif, jpeg, png, swf) */
			$image = getimagesize($this->file["tmp_name"]);
			if ($this->max_image_width > 0) {
				$this->file["width"]  = $image[0];
			}
			else {
				$this->file["width"] = 0;
			}
			if ($this->max_image_height > 0) {
				$this->file["height"] = $image[1];
			}
			else {
				$this->file["height"] = 0;
			}
			
			// test max image size
			if((($this->max_image_width > 0 && $this->file["width"] > $this->max_image_width) || ($this->max_image_height > 0 && $this->file["height"] > $this->max_image_height))) {
				$this->error = $this->get_error(2);
				$this->accepted  = FALSE;
				return FALSE;
			}
			}
		
		// check to see if the file is of type specified
		if(count($this->acceptable_file_types) > 0) {
			if(!empty($this->file["extention"]) && in_array($this->file["extention"], $this->acceptable_file_types)) {
				$this->accepted = TRUE;
			} else { 
				$this->accepted = FALSE;
				$this->error = $this->get_error(3);
			}
		} else { 
			$this->accepted = TRUE;
		}
		
		return (bool) $this->accepted;
	}


	/**
	 * bool save_file ( string path[, int overwrite_mode] );
	 * 
	 * Cleans up the filename, copies the file from PHP's temp location to $path, 
	 * and checks the overwrite_mode
	 * 
	 * @param path		  (string) File path to your upload directory
	 * @param overwrite_mode  (int) 	1 = overwrite existing file
	 * 					2 = rename if filename already exists (file.txt becomes file_copy0.txt)
	 * 					3 = do nothing if a file exists
	 * 
	 */
	function save_file($path, $overwrite_mode="3"){
		if ($this->error) {
			return false;
		}
		
		if (strlen($path)>0) {
			if ($path[strlen($path)-1] != "/") {
				$path = $path . "/";
			}
		}
		$this->path = $path;	
		$copy       = "";	
		$n          = 1;	
		$success    = false;	
		
		if($this->accepted) {
			$this->file["name"] = preg_replace("/[^a-zA-Z0-9._-]+/i", "", str_replace(" ", "_", str_replace("%20", "_", $this->file["name"])));
			
			// Clean up text file breaks
			if(stristr($this->file["type"], "text")) {
				$this->cleanup_text_file($this->file["tmp_name"]);
			}
			
			// get the raw name of the file (without its extenstion)
			if(ereg("(\.)([a-z0-9]{1,8})$", $this->file["name"])) {
				$pos = strrpos($this->file["name"], ".");
				if(!$this->file["extention"]) { 
					$this->file["extention"] = substr($this->file["name"], $pos, strlen($this->file["name"]));
				}
				$this->file['raw_name'] = substr($this->file["name"], 0, $pos);
			} else {
				$this->file['raw_name'] = $this->file["name"];
				if ($this->file["extention"]) {
					$this->file["name"] = $this->file["name"] . $this->file["extention"];
				}
			}

			if (@ini_get('open_basedir') != '') {
				$copyf = 'move_uploaded_file';
			}
			else {
				$copyf = 'copy';
			}
			
			switch((int) $overwrite_mode) {
				case 1: // overwrite mode
					if ($copyf($this->file["tmp_name"], $this->path . $this->file["name"])) {
						$success = true;
					} else {
						$success     = false;
						$this->error = $this->get_error(5);
					}
					break;
				case 2: // create new with incremental extention
					while(file_exists($this->path . $this->file['raw_name'] . $copy . $this->file["extention"])) {
						$copy = "_" . $n;
						$n++;
					}
					$this->file["name"]  = $this->file['raw_name'] . $copy . $this->file["extention"];
					if ($copyf($this->file["tmp_name"], $this->path . $this->file["name"])) {
						$success = true;
					} else {
						$success     = false;
						$this->error = $this->get_error(5);
					}
					break;
				default:
					if(file_exists($this->path . $this->file["name"])){
						$this->error = $this->get_error(4);
						$success     = false;
					} else {
						if (@copy($this->file["tmp_name"], $this->path . $this->file["name"])) {
							$success = true;
						} else {
							$success     = false;
							$this->error = $this->get_error(5);
						}
					}
					break;
			}
			
			if(!$success) { unset($this->file['tmp_name']); }
			return (bool) $success;
		} else {
			$this->error = $this->get_error(3);
			return FALSE;
		}
	}
	
	function return_error () {
		return $this->error;
	}

	function fileinfo($index) {
		return @$this->file[$index];
	}
	
	/**
	 * string get_error(int error_code);
	 * 
	 * Gets the correct error message for language set by constructor
	 * 
	 * @param error_code		(int) error code
	 * 
	 */
	function get_error($error_code='') {
		global $lang;
		$error_message = array();
		$error_code    = (int) $error_code;
		
		if (!$this->max_image_height) $this->max_image_height = $lang->phrase('upload_unspecified');
		if (!$this->max_image_width) $this->max_image_width = $lang->phrase('upload_unspecified');

		$aft = implode(", ", $this->acceptable_file_types);
		$mfs = formatFilesize($this->max_filesize);
		$pathfile = $this->path.$this->file["name"];

		if (is_object($lang)) {
			$lang->assign('this', $this);
			$lang->assign('aft', $aft);
			$lang->assign('mfs', $mfs);
			$lang->assign('pathfile', $pathfile);
			$error_message[0] = $lang->phrase('upload_error_noupload');
			$error_message[1] = $lang->phrase('upload_error_maxfilesize');
			$error_message[2] = $lang->phrase('upload_error_maximagesize');
			$error_message[3] = $lang->phrase('upload_error_wrongfiletype');
			$error_message[4] = $lang->phrase('upload_error_fileexists');
			$error_message[5] = $lang->phrase('upload_error_noaccess');
		}
		else {
			$error_message[0] = 'Es wurde keine Datei hochgeladen';
			$error_message[1] = 'Zugriff verweigert. Konnte Datei nicht zu &quot;{%this->path}&quot; kopieren.';
			$error_message[2] = 'Nur {$aft} Dateien d&uuml;rfen hochgeladen werden.';
			$error_message[3] = 'Maximale Dateigr&ouml;sse &uuml;berschritten. Datei darf nicht gr&ouml;sser als {$mfs} sein.';
			$error_message[4] = 'Maximale Bildgr&ouml;sse &uuml;berschritten. Bild darf nicht gr&ouml;sser als {%this->max_image_width} x {%this->max_image_height} Pixel sein.';
			$error_message[5] = 'Datei &quot;{$pathfile}&quot; existiert bereits.';
		}
		
		// for backward compatability:
		$this->errors[$error_code] = $error_message[$error_code];
		
		return $error_message[$error_code];
	}


	/**
	 * void cleanup_text_file (string file);
	 * 
	 * Convert Mac and/or PC line breaks to UNIX by opening
	 * and rewriting the file on the server
	 * 
	 * @param file			(string) Path and name of text file
	 * 
	 */
	function cleanup_text_file($file){
		$new_file  = '';
		$old_file  = '';
		$fcontents = file($file);
		while (list ($line_num, $line) = each($fcontents)) {
			$old_file .= $line;
			$new_file .= preg_replace('/(\r\n|\r|\n)/', "\r\n", $line);
		}
		if ($old_file != $new_file) {
			// Open the uploaded file, and re-write it with the new changes
			$fp = fopen($file, "w");
			fwrite($fp, $new_file);
			fclose($fp);
		}
	}

}


/*
<readme>

	fileupload-class.php can be used to upload files of any type
	to a web server using a web browser. The uploaded file's name will 
	get cleaned up - special characters will be deleted, and spaces 
	get replaced with underscores, and moved to a specified 
	directory (on your server). fileupload-class.php also does its best to 
	determine the file's type (text, GIF, JPEG, etc). If the user 
	has named the file with the correct extension (.txt, .gif, etc), 
	then the class will use that, but if the user tries to upload 
	an extensionless file, PHP does can identify text, gif, jpeg, 
	and png files for you. As a last resort, if there is no 
	specified extension, and PHP can not determine the type, you 
	can set a default extension to be added.
	
	SETUP:
		Make sure that the directory that you plan on uploading 
		files to has enough permissions for your web server to 
		write/upload to it. (usually, this means making it world writable)
			- cd /your/web/dir
			- chmod 777 <fileupload_dir>
		
		The HTML FORM used to upload the file should look like this:
		<form method="post" action="upload.php" enctype="multipart/form-data">
			<input type="file" name="userfile"> 
			<input type="submit" value="Submit">
		</form>


	USAGE:
		// Create a new instance of the class
		$my_uploader = new uploader;
		
		// OPTIONAL: set the max filesize of uploadable files in bytes
		$my_uploader->max_filesize(90000);

		// OPTIONAL: if you're uploading images, you can set the max pixel dimensions 
		$my_uploader->max_image_size(150, 300); // max_image_size($width, $height)
		
		// UPLOAD the file
		$my_uploader->upload("userfile", "", ".jpg");

		// MOVE THE FILE to its final destination
		//	$mode = 1 ::	overwrite existing file
		//	$mode = 2 ::	rename new file if a file
		//	       			with the same name already 
		//         			exists: file.txt becomes file_copy0.txt
		//	$mode = 3 ::	do nothing if a file with the
		//	       			same name already exists
		$my_uploader->save_file("/your/web/dir/fileupload_dir", int $mode);
		
		// Check if everything worked
		if ($my_uploader->return_error()) {
			echo $my_uploader->return_error() . "<br>";
		
		} else {
			// Successful upload!
			$file_name = $my_uploader->file['name'];
			print($file_name . " was successfully uploaded!");
		
		}
		
</readme>


<license>

	///// fileupload-class.php /////
	Copyright (c) 1999, 2002, 2003 David Fox, Angryrobot Productions
	All rights reserved.
	
	Redistribution and use in source and binary forms, with or without 
	modification, are permitted provided that the following conditions 
	are met:
	1. Redistributions of source code must retain the above copyright 
	   notice, this list of conditions and the following disclaimer.
	2. Redistributions in binary form must reproduce the above 
	   copyright notice, this list of conditions and the following 
	   disclaimer in the documentation and/or other materials provided 
	   with the distribution.
	3. Neither the name of author nor the names of its contributors 
	   may be used to endorse or promote products derived from this 
	   software without specific prior written permission.

	DISCLAIMER:
	THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS "AS IS" 
	AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED 
	TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A 
	PARTICULAR PURPOSE ARE DISCLAIMED.  IN NO EVENT SHALL THE AUTHOR OR 
	CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, 
	SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT 
	LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF 
	USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED 
	AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT 
	LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING 
	IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF 
	THE POSSIBILITY OF SUCH DAMAGE.

</license>

*/
?>
