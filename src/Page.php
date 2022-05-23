<?php

namespace vima\RedKuri;

class Page {
	function preventCaching()
	{
		header("Expires: 0");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("cache-control: no-store, no-cache, must-revalidate");
		header("Pragma: no-cache");
	}

	function isAjax()
	{
		if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			return true;
		}
		return false;
	}

	function isSubmitted()
	{
		if (self::Post('RKFormSubmit') === 'submitted') {
			return true;
		}
		return false;
	}

	function Session($var, $value = null)
	{
		if ($value <> null) {
			$_SESSION[$var] = $value;
		}
		if (isset($_SESSION[$var])) {
			return $_SESSION[$var];
		}
		return null;
	}	
	
	function Get($var, $default=null)
	{
		if (isset($_GET[$var])) {
			return $_GET[$var];
		}
		return $default;
	}

	function Post($var, $default=null)
	{
		if (isset($_POST[$var])) {
			return $_POST[$var];
		}
		return $default;
	}

	function Server($var, $default=null)
	{
		if (isset($_SERVER[$var])) {
			return $_SERVER[$var];
		} 
		return $default;
	}
	
	function Files($var,$default=null)
	{
		if (isset($_FILES[$var])) {
			return $_FILES[$var];
		}
		return $default;
	}
}
