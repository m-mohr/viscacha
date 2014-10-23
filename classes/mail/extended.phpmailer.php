<?php
class my_phpmailer extends phpmailer {

    // Replace the default error_handler
    function error_handler($msg) {
		trigger_error($msg, E_USER_WARNING);
    }
}
?>
