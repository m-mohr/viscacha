<?php
if (defined('VISCACHA_CORE') == false) { die('Error: Hacking Attempt'); }

define('UPLOAD_ERR_FILE_INDEX', 1);
define('UPLOAD_ERR_FILE_SIZE', 2);
define('UPLOAD_ERR_IMAGE_WIDTH', 3);
define('UPLOAD_ERR_IMAGE_HEIGHT', 4);
define('UPLOAD_ERR_FILE_TYPE', 5);
define('UPLOAD_ERR_COPY', 6);
define('UPLOAD_ERR_FILE_EXISTS', 7);

class uploader {

	var $file;
	var $path;
	var $file_types;
	var $error;
	var $max_filesize;
	var $max_image_width;
	var $max_image_height;
	var $new_filename;
	var $copy_func;

	/**
	 * Konstruktor - Initializes the variables.
	 */
	function uploader () {
		$this->file = array();
		$this->path = dirname(__FILE__).DIRECTORY_SEPARATOR;
		$this->file_types = array();
		$this->new_filename = null;
		$this->error = null;
		$this->max_filesize = function_exists('ini_maxupload') ? ini_maxupload() : 0;
		$this->max_image_width = 0;
		$this->max_image_height = 0;
		$this->copy_func = 'move_uploaded_file';
	}


	/**
	 * Set the maximum file size.
	 *
	 * Set the maximum file size in bytes ($size), allowable by the object. 0 = unlimited
	 * NOTE: PHP's configuration file also can control the maximum upload size.
	 * To upload larger files, you'll have to change the php.ini file first.
	 *
	 * @param	int		file size in bytes
	 */
	function max_filesize($size){
		$this->max_filesize = intval($size);
	}


	/**
	 * Set maximum image dimensions.
	 *
	 * Sets the maximum pixel dimensions. Will only be checked if the
	 * uploaded file is an image.
	 *
	 * @param	int		maximum pixel width of image uploads
	 * @param	int		maximum pixel height of image uploads
	 *
	 */
	function max_image_size($width, $height){
		$this->max_image_width  = intval($width);
		$this->max_image_height = intval($height);
	}


	/**
	 * Sets allowed file types.
	 *
	 * Sets the allowed file types. Specify the extensions without leading dot!
	 * If you do not specify any extensions, the followeing will be used:
	 * zip, rar, doc, pdf, txt, gif, png, jpg
	 * If you specify an empty array, all extensions are allowed.
	 *
	 * @param	array		File types/Extensions
	 *
	 */
	function file_types($accept_type = null){
		if (is_array($accept_type) == true) {
		    $this->file_types = array_map('strtolower', $accept_type);
		}
		else {
		    $this->file_types = array('zip','rar','doc','pdf','txt','gif','png','jpg');
		}
	}

	/**
	 * Set the upload path
	 *
	 * @param	string	Path
	 */
	function set_path($path) {
		$this->path = $path;
		if ($path[strlen($path)-1] != '/' && $path[strlen($path)-1] != '\\') {
			$this->path .= '/';
		}
	}

	/**
	 * Rename the uploaded file.
	 *
	 * Renames the uploaded file to $newname. Specify the new name without extension!
	 *
	 * @param	string	New file name (without extension)
	 */
	function rename_file($newname){
		$this->new_filename = $newname;
	}

	/**
	 * Upload a file.
	 *
	 * Checks if the file is acceptable and uploads it to PHP's default upload diretory
	 *
	 * @param	string	Name of form field
	 * @return	boolean
	 */
	function upload($index) {
		global $imagetype_extension;

		if (!isset($_FILES[$index]) || !is_array($_FILES[$index])) {
			$this->error = UPLOAD_ERR_FILE_INDEX;
			return false;
		}


		// Copy PHP's global $_FILES array to a local array
		$indexes = array('extension','size','width','height','tmp_name','raw_name','form','type','name','image','filename');
		foreach ($indexes as $key) {
			$this->file[$key] = isset($_FILES[$index][$key]) ? $_FILES[$index][$key] : null;
		}


		// Set input field name
		$this->file['form'] = $index;
		// Get extension
		$this->file['extension'] = strtolower(get_extension($this->file['name']));
		// Get file size
		if (empty($this->file['size']) == true) {
			$this->file['size'] = filesize($this->file['tmp_name']);
		}
		// Set mime type
		if (empty($this->file['type']) == true) {
			if (function_exists('mime_content_type') == true) {
				$this->file['type'] = mime_content_type($this->file['name']);
			}
		}
		// Check image data (height, width, image)
		if(in_array($this->file['extension'], $imagetype_extension) == true) {
			$this->file['image'] = true;
			$properties = @getimagesize($this->file['tmp_name']);
			if (is_array($properties) == false) {
				$this->file['image'] = false;
			}
			else {
				$this->file['width']  = $properties[0];
				$this->file['height'] = $properties[1];
				if (empty($this->file['type']) == true) {
					$this->file['type'] = image_type_to_mime_type($properties[2]);
				}
			}
		}
		else {
			$this->file['image'] = false;
		}
		// Set raw_name
		$this->file['raw_name'] = substr($this->file['name'], 0, -(strlen($this->file['extension'])+1) );
		$this->file['filename'] = $this->file['name'];

		// test max file size
		if($this->max_filesize > 0 && $this->file['size'] > $this->max_filesize ) {
			$this->error = UPLOAD_ERR_FILE_SIZE;
			return false;
		}
		// test max image size
		if($this->file['image'] == true) {
			if($this->max_image_width > 0 && $this->file['width'] > $this->max_image_width) {
				$this->error = UPLOAD_ERR_IMAGE_WIDTH;
				return false;
			}
			if($this->max_image_height > 0 && $this->file['height'] > $this->max_image_height) {
				$this->error = UPLOAD_ERR_IMAGE_HEIGHT;
				return false;
			}
		}
		// check to see if the file is of type specified
		if(count($this->file_types) > 0) {
			if(in_array($this->file['extension'], $this->file_types) == false) {
				$this->error = UPLOAD_ERR_FILE_TYPE;
				return false;
			}
		}

		if (!is_uploaded_file($this->file['tmp_name'])) {
			$this->copy_func = 'copy';
		}

		return true;
	}

	/**
	 * Save uploaded file.
	 *
	 * Cleans up the filename, copies the file from PHP's temp location to $path,
	 * and checks the overwrite_mode
	 *
	 * @param path		  		string	File path to your upload directory
	 * @param overwrite_mode  	int 	0 = rename if filename already exists (file.txt becomes file_1.txt)
	 *									1 = overwrite existing file
	 * 									2 = do nothing if a file exists
	 * @return	boolean
	 */
	function save_file($path = null, $overwrite_mode = 0){
		if ($this->error != null) {
			return false;
		}

		if ($path != null && strlen($path) > 0) {
			$this->path = $path;
			if ($path[strlen($path)-1] != '/' && $path[strlen($path)-1] != '\\') {
				$this->path .= '/';
			}
		}

		$success = false;

		if ($this->new_filename != null) {
			$this->file['raw_name'] = $this->new_filename;
		}
		else {
			// Secure file name
			$this->file['raw_name'] = str_replace (array('á', 'à', 'â', 'Á', 'À', 'Â'), 			'a', 	$this->file['raw_name']);
			$this->file['raw_name'] = str_replace (array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ë', 'Ê'), 	'e', 	$this->file['raw_name']);
			$this->file['raw_name'] = str_replace (array('í', 'ì', 'î', 'ï', 'Í', 'Ì', 'Î', 'Ï'), 	'i', 	$this->file['raw_name']);
			$this->file['raw_name'] = str_replace (array('ó', 'ò', 'ô', 'Ó', 'Ò', 'Ô'), 			'o', 	$this->file['raw_name']);
			$this->file['raw_name'] = str_replace (array('ú', 'ù', 'û', 'Ú', 'Ù', 'Û'), 			'u', 	$this->file['raw_name']);
			$this->file['raw_name'] = str_replace (array('ä', 'Ä'), 								'ae', 	$this->file['raw_name']);
			$this->file['raw_name'] = str_replace (array('ö', 'Ö'), 								'oe', 	$this->file['raw_name']);
			$this->file['raw_name'] = str_replace (array('ü', 'Ü'), 								'ue', 	$this->file['raw_name']);
			$this->file['raw_name'] = str_replace (array(' ', '%20'), 								'_', 	$this->file['raw_name']);
			$this->file['raw_name'] = str_replace ('ß', 'ss', $this->file['raw_name']);
			$this->file['raw_name'] = preg_replace("/[^a-zA-Z0-9\._-]+/i", '', $this->file['raw_name']);
		}

		$new_path = $this->path.$this->file['raw_name'].'.'.$this->file['extension'];

		switch(intval($overwrite_mode)) {
			case 1: // overwrite mode
				if (call_user_func($this->copy_func, $this->file['tmp_name'], $new_path)) {
					$success = true;
					$this->file['tmp_name'] = $new_path;
				}
				else {
					$success = false;
					$this->error = UPLOAD_ERR_COPY;
				}
			break;
			case 2: // do nothing
				if(file_exists($this->path . $this->file["name"])){
					$this->error = UPLOAD_ERR_FILE_EXISTS;
					$success = false;
				}
				else {
					if (call_user_func($this->copy_func, $this->file['tmp_name'], $new_path)) {
						$success = true;
						$this->file['tmp_name'] = $new_path;
					}
					else {
						$success = false;
						$this->error = UPLOAD_ERR_COPY;
					}
				}
			break;
			default: // create new with incremental extension
				$n = 0;
				while(file_exists($new_path) == true) {
					$n++;
					$new_path =  $this->path.$this->file['raw_name'].'_'.$n.'.'.$this->file['extension'];
				}
				if ($n > 0) {
					$this->file['raw_name'] .= '_'.$n;
				}
				if (call_user_func($this->copy_func, $this->file['tmp_name'], $new_path)) {
					$success = true;
					$this->file['tmp_name'] = $new_path;
				}
				else {
					$success = false;
					$this->error = UPLOAD_ERR_COPY;
				}
		}
		$this->file['filename'] = $this->file['raw_name'].'.'.$this->file['extension'];

		// Clean up text file line breaks
		if(substr($this->file['type'], 0, 4) == 'text') {
			$this->cleanup_text_file();
		}
		if (isset($GLOBALS['filesystem'])) {
			global $filesystem;
			$filesystem->chmod($this->file['tmp_name'], 0666);
		}
		else {
			@chmod($this->file['tmp_name'], 0666);
		}

		return $success;
	}

	/**
	 * Get information from file-Variable
	 *
	 * Gets some information from the $this->file-Variable. There are some information about the uploaded file saved.
	 * Possible Indices are:
	 * string	'extension'
	 * int		'size'
	 * int		'width'
	 * int		'height'
	 * string	'tmp_name'
	 * string	'raw_name'
	 * string	'form'
	 * string	'type'
	 * string	'name'
	 * string	'filename'
	 * boolean	'image'
	 *
	 * @param	string	Index
	 * @return	mixed
	 */
	function fileinfo($index) {
		return isset($this->file[$index]) ? $this->file[$index] : null;
	}

	/**
	 * Checks whether an error occured (true) or not (false).
	 *
	 * @return	boolean
	 */
	function upload_failed() {
		return ($this->error != null);
	}

	/**
	 * Gets the correct error message.
	 *
	 * Methoed tries to use $lang-Object. If not available, hardcoded english phrases will be used.
	 *
	 * @return	string		error message
	 */
	function get_error() {
		if ($this->error == null) {
			return false;
		}

		global $lang;

		if (is_object($lang) && method_exists($lang, 'group_is_loaded') == true && $lang->group_is_loaded('global') == true) {
			switch($this->error) {
				case UPLOAD_ERR_FILE_INDEX:
					$message = $lang->phrase('upload_error_noupload');
				break;
				case UPLOAD_ERR_FILE_SIZE:
					$lang->assign('mfs', formatFilesize($this->max_filesize));
					$message = $lang->phrase('upload_error_maxfilesize');
				break;
				case UPLOAD_ERR_IMAGE_WIDTH:
				case UPLOAD_ERR_IMAGE_HEIGHT:
					$lang->assign('mih', $this->max_image_height > 0 ? numbers($this->max_image_height) : $lang->phrase('upload_unspecified'));
					$lang->assign('miw', $this->max_image_width > 0 ? numbers($this->max_image_width) : $lang->phrase('upload_unspecified'));
					$message = $lang->phrase('upload_error_maximagesize');
				break;
				case UPLOAD_ERR_FILE_TYPE:
					$lang->assign('aft', implode($lang->phrase('listspacer'), $this->file_types));
					$message = $lang->phrase('upload_error_wrongfiletype');
				break;
				case UPLOAD_ERR_COPY:
					$message = $lang->phrase('upload_error_noaccess');
				break;
				case UPLOAD_ERR_FILE_EXISTS:
					$message = $lang->phrase('upload_error_fileexists');
				break;
				default:
					$message = $lang->phrase('upload_error_default');
			}
			if (!empty($this->file['name'])) {
				return "{$this->file['name']}: {$message}";
			}
			else {
				return $message;
			}
		}
		else{
			switch($this->error) {
				case UPLOAD_ERR_FILE_INDEX:
					$message = 'No file has been uploaded.';
				break;
				case UPLOAD_ERR_FILE_SIZE:
					$message = 'Max. filesize reached. The file is not allowed to be bigger than '.formatFilesize($this->max_filesize).'.';
				break;
				case UPLOAD_ERR_IMAGE_WIDTH:
				case UPLOAD_ERR_IMAGE_HEIGHT:
					$mih = $this->max_image_height > 0 ? numbers($this->max_image_height) : 'any';
					$miw = $this->max_image_width > 0 ? numbers($this->max_image_width) : 'any';
					$message = "Max. imagesize reached. Image is not allowed to be greater than {$miw} x {$mih} pixels.";
				break;
				case UPLOAD_ERR_FILE_TYPE:
					$message = 'Only '.implode(', ', $this->file_types).' files are allowed to be uploaded.';
				break;
				case UPLOAD_ERR_COPY:
					$message = 'Access denied. Could not copy file.';
				break;
				case UPLOAD_ERR_FILE_EXISTS:
					$message = 'File already exists.';
				break;
				default:
					$message = 'An unknown error occured while uploading.';
			}
			if (!empty($this->file['name'])) {
				return "{$this->file['name']}: {$message}";
			}
			else {
				return $message;
			}
		}
	}


	/**
	 * Converts line breaks in text files.
	 *
	 * Convert Mac (\r) and/or Unix (\n) line breaks to Windows (\r\n) by opening and rewriting the file on the server
	 */
	function cleanup_text_file(){
		$fcontents = @file_get_contents($this->file['tmp_name']);
		$fcontents = preg_replace("/(\r\n|\r|\n)/", "\r\n", $fcontents);
		@file_put_contents($this->file['tmp_name'], $fcontents);
	}

}
?>
