<?php

require_once("../vendor/autoload.php");

use ZLX\Cache\Cache;
use ZLX\Session\Session;

Cache::init([
    'prefix' => "zlx_example_session",
    'instances' => [
        'default' => [
            'engine' => 'memcached',
            'duration' => '+30 minutes',
        ]
    ]
]);

Session::init([
    'cache_instance' => 'default', // Which instance to use based on ZLX Cache configuration (OPTIONAL, defaults to 'default')
    'cookie_name' => 'ZLX_sess', // Which cookie name to use to store session id (OPTIONAL, defaults to 'ZLX_sess')
    'session_scret' => 'wxLl88ISVTz7lvHgZvOSKrxOWI7MjLA1', // Secret used to encrypt and decrypt with ZLX Security (OPTIONAL, defaults to '')
    'security_salt' => 'bhY4dZ5bEeru9e1XlObtY9Mc95cfDcrQ' // SALT used in ZLX Security for this session (OPTIONAL, defaults to null)
]);

?>
