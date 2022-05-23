<?php

use vima\RedKuri as RK;

error_reporting(0);
date_default_timezone_set('Europe/London');

require_once __DIR__.'/../../vendor/autoload.php';

require('../protected/settings.php');
require(RK_REDKURIPATH.'startup.php');

set_error_handler('errorHandler');
set_exception_handler('exceptionHandler');

function errorHandler($errno, $errstr, $errfile='', $errline='') {
    //Logger::log(Logger::ERROR, 'Error', $errno, $errstr . '<br>' . $_SERVER['REQUEST_URI'] . '<br>'.RK\debug_string_backtrace(), null);
    if ($errno < 2) {
		echo 'Error', $errno, $errstr . '<br>' . $_SERVER['REQUEST_URI'] . '<br>'.RK\debug_string_backtrace();
        die();
    }
}

function exceptionHandler($exception) {
    //Logger::log(Logger::ERROR, 'Exception', '', $exception->getMessage().'<br>'.$_SERVER['REQUEST_URI'].'<br>'.RK\debug_string_backtrace(), null);
	echo '<pre>'.$exception->getMessage().'<br>'.$_SERVER['REQUEST_URI'].'<br>'.RK\debug_string_backtrace().'</pre>';
    die();
}

session_start();

$fw = new RK\Firewall();
$check = $fw->protect();

if ($check !== true) {
	systemLog($check);
	header('Location: https://fabtrackr.com');
	die();
}

function foody() {
	static $__db;

	if (!($__db)) {
		$__db = new RK\UchikiDatabase(RK_DB_HOST, RK_DB_DATABASE, RK_DB_USERNAME, RK_DB_PASSWORD, 'mysql');
	}
	return $__db;
}

$path = explode('?', substr($_SERVER['REQUEST_URI'], strlen(RK_BASEPATH)));

if (substr($path[0], -1) == '/') {
	$path[0] = substr($path[0], 0, -1);
}
$path[0] = explode('/', $path[0]);

switch ($path[0][0]) {
	case '':
		$path[0][0] = 'index.php';
		break;
	case 'admin':
		array_shift($path[0]);
		if (isset($path[0][0])) {
			$path[0][0] = 'admin_'.$path[0][0].'.php';
		}
		break;
	case 'js':
		$path[0][0] = 'js/'.$path[0][1];
	default:
		$path[0][0] = $path[0][0].'.php';
		break;
}

function argument($position = 0) {
	global $path;

	if ($position < 0) { $position = count($path[0])+$position; }

	if (isset($path[0][$position])) {
		return $path[0][$position];
	}
	return null;
}

if (isset($path[0][0]) && file_exists(RK_PROTECTEDPATH.'views/'.$path[0][0]))
	require(RK_PROTECTEDPATH.'views/'.$path[0][0]);
else 
	if (file_exists(RK_PROTECTEDPATH.'views/404.php'))
		require(RK_PROTECTEDPATH.'views/404.php');
	else {
		echo '<style>body {font-family:sans-serif; color:#44f; font-weight:normal;}</style>';
		echo '<h1>404 File Not Found</h1>';
		echo '<p><i>Red Kuri Framework</i></p>';
	}
