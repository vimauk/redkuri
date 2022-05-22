<?php

namespace RedKuri;

/*
 * Password hashing with PBKDF2.
 * Author: havoc AT defuse.ca
 * www: https://defuse.ca/php-pbkdf2.htm
 */

// These constants may be changed without breaking existing hashes.
define("PBKDF2_HASH_ALGORITHM", "sha256");
define("PBKDF2_ITERATIONS", 1000);
define("PBKDF2_SALT_BYTE_SIZE", 24);
define("PBKDF2_HASH_BYTE_SIZE", 24);

define("HASH_SECTIONS", 4);
define("HASH_ALGORITHM_INDEX", 0);
define("HASH_ITERATION_INDEX", 1);
define("HASH_SALT_INDEX", 2);
define("HASH_PBKDF2_INDEX", 3);

function create_hash($password)
{
    // format: algorithm:iterations:salt:hash
    $salt = base64_encode(mcrypt_create_iv(PBKDF2_SALT_BYTE_SIZE, MCRYPT_DEV_URANDOM));
    return PBKDF2_HASH_ALGORITHM . ":" . PBKDF2_ITERATIONS . ":" .  $salt . ":" .
        base64_encode(pbkdf2(
            PBKDF2_HASH_ALGORITHM,
            $password,
            $salt,
            PBKDF2_ITERATIONS,
            PBKDF2_HASH_BYTE_SIZE,
            true
        ));
}

function validate_password($password, $correct_hash)
{
    $params = explode(":", $correct_hash);
    if(count($params) < HASH_SECTIONS)
       return false;
    $pbkdf2 = base64_decode($params[HASH_PBKDF2_INDEX]);
    return slow_equals(
        $pbkdf2,
        pbkdf2(
            $params[HASH_ALGORITHM_INDEX],
            $password,
            $params[HASH_SALT_INDEX],
            (int)$params[HASH_ITERATION_INDEX],
            strlen($pbkdf2),
            true
        )
    );
}

// Compares two strings $a and $b in length-constant time.
function slow_equals($a, $b)
{
    $diff = strlen($a) ^ strlen($b);
    for($i = 0; $i < strlen($a) && $i < strlen($b); $i++)
    {
        $diff |= ord($a[$i]) ^ ord($b[$i]);
    }
    return $diff === 0;
}

/*
 * PBKDF2 key derivation function as defined by RSA's PKCS #5: https://www.ietf.org/rfc/rfc2898.txt
 * $algorithm - The hash algorithm to use. Recommended: SHA256
 * $password - The password.
 * $salt - A salt that is unique to the password.
 * $count - Iteration count. Higher is better, but slower. Recommended: At least 1000.
 * $key_length - The length of the derived key in bytes.
 * $raw_output - If true, the key is returned in raw binary format. Hex encoded otherwise.
 * Returns: A $key_length-byte key derived from the password and salt.
 *
 * Test vectors can be found here: https://www.ietf.org/rfc/rfc6070.txt
 *
 * This implementation of PBKDF2 was originally created by https://defuse.ca
 * With improvements by http://www.variations-of-shadow.com
 */
function pbkdf2($algorithm, $password, $salt, $count, $key_length, $raw_output = false)
{
    $algorithm = strtolower($algorithm);
    if(!in_array($algorithm, hash_algos(), true))
        die('PBKDF2 ERROR: Invalid hash algorithm.');
    if($count <= 0 || $key_length <= 0)
        die('PBKDF2 ERROR: Invalid parameters.');

    $hash_length = strlen(hash($algorithm, "", true));
    $block_count = ceil($key_length / $hash_length);

    $output = "";
    for($i = 1; $i <= $block_count; $i++) {
        // $i encoded as 4 bytes, big endian.
        $last = $salt . pack("N", $i);
        // first iteration
        $last = $xorsum = hash_hmac($algorithm, $last, $password, true);
        // perform the other $count - 1 iterations
        for ($j = 1; $j < $count; $j++) {
            $xorsum ^= ($last = hash_hmac($algorithm, $last, $password, true));
        }
        $output .= $xorsum;
    }

    if($raw_output)
        return substr($output, 0, $key_length);
    else
        return bin2hex(substr($output, 0, $key_length));
}

if (!isset($_SESSION)) session_start();

interface RKSecureProvider
{
	function checkUserExists($username);
	function updateLastLogin($username);
	function lockoutUser($username);
	function recordLoginFailure($username);
	function loginFailures($username);
	function getPasswordHash($username);
	function checkInGroup($groupname);
	function log($message);
}

class RKSecureMySQLProvider implements RKSecureProvider
{
	function __construct($db)
	{
		$this->db = $db;
	}

	function checkUserExists($username)
	{
		$result = \ORM::for_table('users')->where('username', $username)->where_null('tombstonetime')->findMany();
		return $result;
	}
	
	function updateLastLogin($username, $loginfailures = null)
	{
		if (null === $loginfailures) {
			$user = \ORM::for_table('users')
				->where('username', $username)
				->findOne();
			$user->lastlogin = date('Y-m-d H:i:s');
			$user->save();
		} else {
			$user = \ORM::for_table('users')
				->where('username', $username)
				->findOne();
			$user->lastlogin = date('Y-m-d H:i:s');
			$user->loginfailures = 0;
			$user->save();
		}
	}
	
	function lockoutUser($username)
	{
		$user = \ORM::for_table('users')
			->where('username', $username)
			->findOne();
		$user->lockouttime = date('Y-m-d H:i:s');
		$user->save();
		
	}
	
	function recordLoginFailure($username)
	{
		$user = \ORM::for_table('users')
			->where('username', $username)
			->findOne();
		if (false === $user) return false;
		$user->loginfailures += 1;
		$user->save();
	}

	function isLocked($username) {
		$user = \ORM::for_table('users')
			->where('username', $username)
			->findOne();

		if (false === $user) return false;

		if ($user->lockouttime === null) return false;

		if (time() - strtotime($user->lockouttime) > 10) {
			$user->loginfailures = 0;
			$user->lockouttime = null;
			$user->save();
			return false;
		}
		return $user->lockouttime;
	}
	
	function loginFailures($username)
	{
		$result = \ORM::for_table('users')
			->select('loginfailures')
			->where('username', $username)
			->findOne();
		if (false === $result) return false;
		return $result->loginfailures;
	}
	
	function getPasswordHash($username)
	{
		$result = \ORM::for_table('users')
			->select('password')
			->where('username', $username)
			->where_null('lockouttime')
			->where_null('tombstonetime')
			->findOne();
		if (false === $result) return false;
		
		return $result->password;
		
//		$res = foody()->sql("SELECT password FROM users WHERE username=? AND (status=? OR (status=? AND (lockouttime IS NULL OR TIME_TO_SEC(TIMEDIFF(NOW(),lockouttime)) > 50)))", array($username, user::USER_ACTIVE, user::USER_LOCKED));
	}
	
	function checkInGroup($groupname)
	{
		return false;
	}

	function log($message) {}
}

class RKSecureSocialProvider implements RKSecureProvider
{
	function __construct($db)
	{
	}

	function checkUserExists($username)
	{
//		$result = \ORM::for_table('users')->where('username', $username)->where_null('tombstonetime')->findMany();
		return $result;
	}
	
	function updateLastLogin($username, $loginfailures = null)
	{
		if (null === $loginfailures) {
			$user = \ORM::for_table('users')
				->where('username', $username)
				->findOne();
			$user->lastlogin = date('Y-m-d H:i:s');
			$user->save();
		} else {
			$user = \ORM::for_table('users')
				->where('username', $username)
				->findOne();
			$user->lastlogin = date('Y-m-d H:i:s');
			$user->loginfailures = 0;
			$user->save();
		}
	}
	
	function lockoutUser($username)
	{
		$user = \ORM::for_table('users')
			->where('username', $username)
			->findOne();
		$user->lockouttime = date('Y-m-d H:i:s');
		$user->save();
		
	}
	
	function recordLoginFailure($username)
	{
		$user = \ORM::for_table('users')
			->where('username', $username)
			->findOne();
		if (false === $user) return false;
		$user->loginfailures += 1;
		$user->save();
	}

	function isLocked($username) {
		$user = \ORM::for_table('users')
			->where('username', $username)
			->findOne();

		if (false === $user) return false;

		if ($user->lockouttime === null) return false;

		if (time() - strtotime($user->lockouttime) > 10) {
			$user->loginfailures = 0;
			$user->lockouttime = null;
			$user->save();
			return false;
		}
		return $user->lockouttime;
	}
	
	function loginFailures($username)
	{
		$result = \ORM::for_table('users')
			->select('loginfailures')
			->where('username', $username)
			->findOne();
		if (false === $result) return false;
		return $result->loginfailures;
	}
	
	function getPasswordHash($username)
	{
		$result = \ORM::for_table('users')
			->select('password')
			->where('username', $username)
			->where_null('lockouttime')
			->where_null('tombstonetime')
			->findOne();
		if (false === $result) return false;
		
		return $result->password;
		
//		$res = foody()->sql("SELECT password FROM users WHERE username=? AND (status=? OR (status=? AND (lockouttime IS NULL OR TIME_TO_SEC(TIMEDIFF(NOW(),lockouttime)) > 50)))", array($username, user::USER_ACTIVE, user::USER_LOCKED));
	}
	
	function checkInGroup($groupname)
	{
		return false;
	}

	function log($message) {}
}

class Secure
{
	const USER_LOCKED = 0;
	const USER_ACTIVE = 1;

	static protected $provider;
	
	public function __construct(RKSecureProvider $provider)
	{
		self::$provider = $provider;
	}
	
	static function userLoggedIn() {
		if (strlen(RKRequest::Session('rkusername')) > 0) {
			return self::$provider->checkUserExists(RKRequest::Session('rkusername'));
		}
		return false;
	}

	static function userLogout()
	{
		self::$provider->log('User Logout - '.RKRequest::Session('rkusername'));
		RKRequest::Session('rkusername', '');
	}

	static function username()
	{
		return RKRequest::Session('rkusername');
	}

	static function checkPassword($username, $password)
	{
		$username = strtolower($username);

		$hash = self::$provider->getPasswordHash($username);

		if ($password == $hash) {
			// Password is not encrypted so we need to encrypt and store
			return true;
		}
		if (false === $hash) return false;

		if (validate_password($password, $hash)) {
			return true;
		}
				
		return false;
	}

	static function numberFailures($username) {
		return self::$provider->numberFailures($username);
	}

	static function loginInterface() {
		RKRequest::Session('rkusername_attempt', RKRequest::Post('rkusername'));
		
		require(RK_PROTECTEDPATH.'views/login.php');
		die();
	}

	static function onlyUser($username) {
		if (!userLoggedIn()) {
			redirect(RK_BASEPATH);
			die();
		}
		if (RKRequest::Session('rkusername') != $username) {
			redirect(RK_BASEPATH);
			die();
		}
	}
	
	static function onlyGroup($groupname) {
		if (!userLoggedIn()) {
			redirect(RK_BASEPATH);
			die();
		}
		if (!self::$provider->checkInGroup(RKRequest::Session('rkusername'))) {
			redirect(RK_BASEPATH);
			die();
		}
	}

	static function forceLogin($username, $password) {
		$username = strtolower($username);
		if (self::checkPassword($username, $password)) {
			self::$provider->updateLastLogin($username);
			RKRequest::Session('rkusername', $username);
		} else {
			self::loginInterface();
		}
	}

	static function securePage() {

		// First check for logout requests

		if ((RKRequest::Get('rklogout') !== null) || (RKRequest::Post('rklogout') !== null) ||
			(RKRequest::Get('logout') !== null) || (RKRequest::Post('logout') !== null)) {
			self::userLogout();
			header('Location: '.RK_HOST);
			exit();
		}

		// If the user is not logged in

		if (!self::userLoggedIn()) {

				if (RKRequest::Post('rkusername')) {

					$username = strtolower(RKRequest::Post('rkusername'));
					$password = RKRequest::Post('rkpassword');

					if (self::$provider->isLocked($username) !== false) {
						RKRequest::Session('rkloginerror', 'Account is currently locked out');
						RKRequest::Session('rklockout', true);
						self::$provider->log('Login attempt while locked out - '.$username);
//						sleep(2);
						self::loginInterface();
					} else {
						if (self::checkPassword($username, $password)) {
							RKRequest::Session('rkloginerror', '');
							self::$provider->updateLastLogin($username);
							RKRequest::Session('rkusername', $username);
							RKRequest::Session('rklockout', false);
							self::$provider->log('User Logged in - '.$username);
						} else {
							RKRequest::Session('rkloginerror', 'Incorrect username or password');
							self::$provider->recordLoginFailure($username);
							if (self::$provider->loginFailures($username) > 10) {
								self::$provider->lockoutUser($username);
								RKRequest::Session('rklockout', true);
							}
							self::$provider->log('User Login failure - '.$username);
//							sleep(2);
							self::loginInterface();
						}
					}
				} else {
					RKRequest::Session('rkloginerror', '');
					self::loginInterface();
				}
		} else {
//			self::$provider->updateLastLogin($username);
		}
	}
}
