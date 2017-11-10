# ZLX Session
===============

Singleton for Session using ZLX Cache and ZLX Security

## Installation

In your ```composer.json``` file include:

```
"require": {
    "aledefreitas/zlx_session": "*"
}
```

Or you can require it with composer:

```
composer require aledefreitas/zlx_session
```

## Requirements

- PHP 7.x +


## Usage

In your bootstrap file, start the static class with:

```PHP
Session::init([
    'cache_instance' => 'default', // Which instance to use based on ZLX Cache configuration (OPTIONAL, defaults to 'default')
    'cookie_name' => 'ZLX_sess', // Which cookie name to use to store session id (OPTIONAL, defaults to 'ZLX_sess')
    'session_scret' => 'wxLl88ISVTz7lvHgZvOSKrxOWI7MjLA1', // Secret used to encrypt and decrypt with ZLX Security (OPTIONAL, defaults to '')
    'security_salt' => 'bhY4dZ5bEeru9e1XlObtY9Mc95cfDcrQ' // SALT used in ZLX Security for this session (OPTIONAL, defaults to null)
])
```

===============

To save data into the Session, you can use ```Session::set()```

#### set(string $key, mixed $value)
_returns boolean_

```php
Session::set('Test', [ 'key' => 'value' ]);
```

===============

To retrieve data inside the Session, you can use ```Session::get()```

#### get(string $key)
_returns mixed_

```PHP
Session::get('Test');
```

===============

To get the current Session ID, you can use ```Session::getSessionId()```

#### getSessionId()
_returns string_

```php
Session::getSessionId();
```

===============

To destroy a Session, you can use ```Session::destroy()```

#### destroy()
_returns void_

```php
Session::destroy();
```
