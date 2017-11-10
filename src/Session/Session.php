<?php
/**
 * ZLX Session
 *
 * @author		Alexandre de Freitas Caetano <https://github.com/aledefreitas>
 */
namespace ZLX\Session;

use ZLX\Cache\Cache;
use ZLX\Security\Security;

/**
 * Session using ZLX Cache
 */
class Session
{
	/**
	 * Session ID
	 *
	 * @var string
	 */
	private $session_id = '';

	/**
	 * Session's stored data
	 *
	 * @var array
	 */
	private $data = [];

	/**
	 * Cookie name
	 * Defaults to: 'ZLX_sess'
	 *
	 * @var string
	 */
	private $cookie_name = 'ZLX_sess';

	/**
	 * Which cache instance to use on ZLX\Cache\Cache
	 * Defaults to: 'default'
	 *
	 * @var string
	 */
	private $cache_instance = 'default';

	/**
	 * Secret passphrase used for encrypting and decrypting the session cookie
	 *
	 * @var string
	 */
	private $session_secret = '';

	/**
	 * Holds the instance used for the session
	 *
	 * @var ZLX\Session\Session
	 */
	private static $_instance = null;

	/**
	 * Constructor method for Session
	 *
	 * @param	array 					$config			Configs
	 * @param	ZLX\Security\Security	$Security
	 *
	 * @return void
	 */
	private function __construct(array $config = [], Security $Security)
	{
		// Dependency Injection for ZLX\Security\Security
		$this->Security = $Security;

		/**
		 * The following lines insert the custom configs inside the instance
		 */
		$this->cookie_name = isset($config['cookie_name']) && trim($config['cookie_name']) !== '' ? $config['cookie_name'] : $this->cookie_name;
		$this->session_secret = isset($config['session_secret']) ? $config['session_secret'] : $this->session_secret;
		$this->cache_instance = isset($config['cache_instance']) && trim($config['cache_instance']) !== '' ? $config['cache_instance'] : $this->cache_instance;

		$this->host_name = $_SERVER['SERVER_NAME'];

		// Checks if the user already has a Session ID
		if(isset($_COOKIE[$this->cookie_name])) {
			// If so, we decrypt the cookie
			$this->session_id = $this->Security->decrypt(hex2bin($_COOKIE[$this->cookie_name]), $this->session_secret);
		}

		// Stores the data into memory
		$this->data = $this->getSessionData();

		// Checks whether the session is valid still
		if(!$this->isValid()) {
			// If it isn't, destroys and starts a new one
			$this->destroySession();
			$this->startSession();
		}
	}

	/**
	 * Initializes the static class with its configs
	 *
	 * @param	array 		$config
	 *
	 * @return false
	 */
	public static function init(array $config = [])
	{
		$salt = isset($config['security_salt']) ? $config['security_salt'] : null;

		self::$_instance = new self($config, new Security($salt));
		self::$_instance->keepAlive();
	}

	/**
	 * Returns the current instance
	 *
	 * @return ZLX\Session\Session
	 */
	private static function getInstance()
	{
		return self::$_instance;
	}

	/**
	 * Starts a new Session
	 *
	 * @return void
	 */
	private function startSession()
	{
		$this->session_id = $this->Security->hash(uniqid() . microtime() . $this->session_secret);
		$fingerprint = $this->getFingerPrint();

		setcookie($this->cookie_name, bin2hex($this->Security->encrypt($this->session_id, $this->session_secret)), null, "/", $this->host_name, false, true);

		$this->data['session_id'] = $this->session_id;
		$this->data["since"] = strtotime("now");
		$this->data["fingerprint"] = $fingerprint;

		$this->save();
	}

	/**
	 * Creates a fingerprint based on a hash of session_secret, host name, user's ip, user agent, and session id
	 *
	 * @return string
	 */
	private function getFingerPrint()
	{
		return $this->Security->hash($this->session_id . $_SERVER['REMOTE_ADDR'] . $this->session_secret . $_SERVER['HTTP_USER_AGENT'] . $this->host_name);
	}

	/**
	 * Gets the session data stored in cache
	 *
	 * @return array
	 */
	private function getSessionData()
	{
		$data = Cache::get("Session.".$this->session_id, $this->cache_instance);
		$data = json_decode($data, true);

		if(!$data) {
			return [];
		}

		return $data;
	}

	/**
	 * Returns whether the session is valid or not
	 *
	 * @return boolean
	 */
	private function isValid()
	{
		return $this->session_id && sizeof($this->data)>0 && $this->data['fingerprint'] == $this->getFingerPrint();
	}

	/**
	 * Saves the session current state
	 *
	 * @return boolean
	 */
	private function save()
	{
		if(trim($this->session_id) == '') return false;

		return Cache::set("Session.".$this->session_id, json_encode($this->data), $this->cache_instance);
	}

	/**
	 * Saves the session again to keep it fresh in cache
	 *
	 * @return void
	 */
	private function keepAlive()
	{
		$this->save();
	}

	/**
	 * Destroys the session
	 *
	 * @return void
	 */
	private function destroySession()
	{
		Cache::delete("Session.".$this->session_id, $this->cache_instance);
		setcookie($this->cookie_name, null, 1, "/",  $this->host_name, false, true);
		$this->data = [];
	}

	/**
	 * Returns the id for this session
	 *
	 * @return string
	 */
	public static function getSessionId()
	{
		$instance = self::getInstance();

		if($instance) {
			return $instance->session_id;
		}

		return '';
	}

	/**
	 * Destroys the session
	 *
	 * @return void
	 */
	public static function destroy()
	{
		$instance = self::getInstance();

		if($instance) {
			$instance->destroySession();
		}
	}

	/**
	 * Searches and returns a key inside this session stored data
	 *
	 * @param	string		$key
	 *
	 * @return mixed
	 */
	public static function get($key)
	{
		$instance = self::getInstance();

		if(!isset($instance->data['values'][$key])) {
			return false;
		}

		return $instance->data['values'][$key];
	}

	/**
	 * Stores a new value in this session data
	 *
	 * @param	string		$key
	 * @param	mixed		$value
	 *
	 * @return boolean
	 */
	public static function set($key, $value)
	{
		$instance = self::getInstance();

		$instance->data['values'][$key] = $value;

		return $instance->save();
	}
}
