<?php

// set the JavaScript variable to the submitted text.
// textinputs is an array, each element corresponding to the (url-encoded)
// value of the text control submitted for spell-checking
function print_textinputs_var() {
	foreach( $_POST['textinputs'] as $key=>$val ) {
		# $val = str_replace( "'", "%27", $val );
		echo "textinputs[$key] = decodeURIComponent(\"" . $val . "\");\n";
	}
}

// make declarations for the text input index
function print_textindex_decl( $text_input_idx ) {
	echo "words[$text_input_idx] = [];\n";
	echo "suggs[$text_input_idx] = [];\n";
}

// set an element of the JavaScript 'words' array to a misspelled word
function print_words_elem( $word, $index, $text_input_idx ) {
	echo "words[$text_input_idx][$index] = '" . escape_quote( $word ) . "';\n";
}


// set an element of the JavaScript 'suggs' array to a list of suggestions
function print_suggs_elem( $suggs, $index, $text_input_idx ) {
	echo "suggs[$text_input_idx][$index] = [";
	foreach( $suggs as $key=>$val ) {
		if( $val ) {
			echo "'" . escape_quote( $val ) . "'";
			if ( $key+1 < count( $suggs )) {
				echo ", ";
			}
		}
	}
	echo "];\n";
}

// escape single quote
function escape_quote( $str ) {
	return str_replace("'", "\\'", $str);
}


// handle a server-side error.
function error_handler( $err ) {
	echo "error = '" . escape_quote( $err ) . "';\n";
}

// get the list of misspelled words. Put the results in the javascript words array
// for each misspelled word, get suggestions and put in the javascript suggs array
function print_checker_results() {
	global $config, $lang;
	
	$aspell_err = "";
	
	if ($config['pspell'] == 'pspell') {
		include('classes/spellchecker/pspell.class.php');
	}
	elseif ($config['pspell'] == 'mysql') {
		include('classes/spellchecker/mysql.class.php');
		global $db;
		$path = $db;
	}
	else {
		include('classes/spellchecker/php.class.php');
		$path = 'classes/spellchecker/dict/';
	}

	$sc = new spellchecker($lang->phrase('spellcheck_dict'),$config['spellcheck_ignore'],$config['spellcheck_mode'], TRUE);
	if (isset($path)) {
		$sc->set_path($path);
	}
	$sc->init();

	$x = $sc->error();
	if (!empty($x)) {
		$aspell_err .= $sc->error();
	}
	else {
		$count = count($_POST['textinputs']);
		for( $i = 0; $i < $count; $i++ ) {
			$text = @utf8_decode(urldecode($_POST['textinputs'][$i]));
			$lines = explode( "\n", $text );
			print_textindex_decl( $i );
			$index = 0;
			foreach( $lines as $value ) {
				$b1 = t1();
				$mistakes = $sc->check_text($value);
				$suggestions = $sc->suggest_text($mistakes);
				foreach ($mistakes as $word) {
					print_words_elem($word, $index, $i);
			  		print_suggs_elem($suggestions[$word], $index, $i);
			  		$index++;
				}
			}
		}
	}
	if(!empty($aspell_err)) {
		$aspell_err = "Error executing {$config['pspell']}:\\n$aspell_err";
		error_handler( $aspell_err );
	}
	@file_put_contents('temp/spellchecker_benchmark.dat', $sc->benchmark());
}

?>
