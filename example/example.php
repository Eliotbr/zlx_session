<?php

require_once("./bootstrap.php");

Session::set("Test", [ 'key' => 'value' ]);

var_dump(Session::get("Test"));

var_dump(Session::getSessionId());

?>
