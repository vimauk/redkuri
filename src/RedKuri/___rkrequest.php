<?php

/*
 *	 RedKuri Framework
 *
 *   Author: Anthony Fearn
 *   Copyright  VIMA 2003 - 2015
 */

namespace vima\RedKuri;

/*
 *	 A HTTP Request handler
 */
 
class RKRequest
{
	static public function argument($position = 0) {
		global $path;

		if (isset($path[0][$position])) {
			return $path[0][$position];
		}
		return null;
	}

	static public function isSubmitted()
	{
		if (RKRequest::Post('RKFormSubmit') === 'submitted') {
			return true;
		}
		return false;
	}

	static public function Get($var, $default=null)
	{
		if (isset($_GET[$var])) {
			return $_GET[$var];
		} else {
			return $default;
		}
	}

	static public function Post($var, $default=null)
	{
		if (isset($_POST[$var])) {
			return $_POST[$var];
		} else {
			return $default;
		}
	}

	static public function Server($var, $default=null)
	{
		if (isset($_SERVER[$var])) {
			return $_SERVER[$var];
		} else {
			return $default;
		}
	}

	static public function Session($var, $value=null)
	{
		if ($value !== null) {
			$_SESSION[$var] = $value;
		}
		if (isset($_SESSION[$var])) {
			return $_SESSION[$var];
		}
		return null;
	}

	static public function isAjax()
	{
		if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			return true;
		}
		return false;
	}
}
