<?php

class gdbbp_Error {
    var $errors = array();

    function __construct() { }

    function add($code, $message, $data) {
        $this->errors[$code][] = array($message, $data);
    }
}

?>