<?php
class my_phpmailer extends phpmailer {

    // Replace the default error_handler
    function error_handler($msg) {
		error($msg,'javascript: history.back(-1);');
    }
}
?>