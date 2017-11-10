<?php

require_once("./bootstrap.php");

use ZLX\Session\Session;

var_dump(Session::set("Test", [ 'key' => 'value' ]));

var_dump(Session::get("Test"));

var_dump(Session::getSessionId());

?>
