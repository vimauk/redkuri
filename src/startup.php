<?php

namespace vima\RedKuri;

define ('RK_VERSION', '4.0');

if ( !defined('__DIR__') ) define('__DIR__', dirname(__FILE__));
if ( !defined('RK_REDKURIPATH') ) define ('RK_REDKURIPATH', __DIR__.'/');

$RKStart = microtime(true);

//set_error_handler('RKerror');
//set_exception_handler('RKexception');

if (RK_APP_TYPE != 'Live') {
	error_reporting(E_ALL);
	ini_set('display_errors', true);
} else {
	error_reporting(E_ERROR | E_WARNING | E_PARSE);
}

function debug($var) {
	if (RK_APP_TYPE != 'Live') {
		echo '<div style="font-family:sans-serif;color:#222;background:#fafafa;padding:4px;font-size:9pt;padding:20px 10px;position:fixed;left:0px;right:0px;bottom:0px;">';
		echo '<b>Red Kuri Debug</b><br>';
		echo debug_string_backtrace();
		echo '<br/>';
		echo '</div>';
		die();
	} else {
		systemLog($_SERVER['REQUEST_URI'].'<br>'.$var.'<br>'.debug_string_backtrace(), 'DEBUG');
		RKliveError();

	}
}
function generateCallTrace() {
    $e = new \Exception();
    $trace = explode("\n", $e->getTraceAsString());
    // reverse array to make steps line up chronologically
    $trace = array_reverse($trace);
    array_shift($trace); // remove {main}
    array_pop($trace); // remove call to this method
    $length = count($trace);
    $result = array();
    
    for ($i = 0; $i < $length; $i++)
    {
        $result[] = ($i + 1)  . ')' . substr($trace[$i], strpos($trace[$i], ' ')); // replace '#someNum' with '$i)', set the right ordering
    }
    
    return "\t" . implode("\n\t", $result);
}

function debug_string_backtrace() {
	ob_start();
	debug_print_backtrace(false);
	$trace = ob_get_contents();
	ob_end_clean();

//    echo generateCallTrace();

    $trace = preg_replace ('/^#0\s+' . __FUNCTION__ . "[^\n]*\n/", '', $trace, 1);

	// Renumber backtrace items.
	
	$trace = preg_replace_callback(
        '/^#(\d+)/m',
        function ($matches) {
            return strtolower('#'.($matches[1]-1));
        },
        $trace
    );
	
	//	$trace = preg_replace ('/^#(\d+)/me', '\'#\' . ($1 - 1)', $trace);

	$trace = preg_replace_callback(
        '/^#(\d+)/m',
        function ($matches) {
            return strtolower('<br>'.$matches[1]);
        },
        $trace
    );

//	$trace = preg_replace ('/^#(\d+)/me', "'<br>$1'", $trace);

	return $trace;
}

function RKliveError() {
	if(headers_sent()) {
		echo '<script type="text/javascript">location.href="'.RK_BASEPATH.'error";</script>';
	} else {
		header('Location: '.RK_BASEPATH.'error');
	}
	die();
}
 
function RKerror($errno, $errstr, $errfile='', $errline='') {
	if (RK_APP_TYPE != 'Live') {
		if (stripos($errstr, 'file_get_contents') !== false) return;
		if (stripos($errstr, 'imagecreate') !== false) return;

		echo '<div style="font-family:sans-serif;color:#222;background:#fafafa;padding:4px;font-size:9pt;padding:20px 10px;position:fixed;left:0px;right:0px;bottom:0px;">';
		echo '<i>Red Kuri Error</i> ';
		echo "$errno : $errstr";
		echo " at line $errline in $errfile<br><pre>";
		echo debug_string_backtrace();
		echo '<br/>';
		echo '</div>';
		die();
	} else {
		if (stripos($errstr, 'file_get_contents') !== false) return;
		if (stripos($errstr, 'imagecreate') !== false) return;
		RKliveError();
	}
}

function RKexception($exception) {
	$errstr = $exception->getMessage();
	if (RK_APP_TYPE != 'Live') {
		if (stripos($errstr, 'file_get_contents') !== false) return;
		if (stripos($errstr, 'imagecreate') !== false) return;
		echo '<div style="font-family:sans-serif;color:#222;background:#fafafa;padding:4px;font-size:9pt;padding:20px 10px;position:fixed;left:0px;right:0px;bottom:0px;">';
		echo 'Red Kuri Exception<br>';
		echo '<b>Uncaught exception: ' . $exception->getMessage().'</b><br>';
		echo '<br/>';
		echo '<pre>'.debug_string_backtrace().'</pre>';
		echo '</div>';
		die();
	} else {
		if (stripos($errstr, 'file_get_contents') !== false) return;
		if (stripos($errstr, 'imagecreate') !== false) return;
		RKliveError();
	}
}

function RKautoload($class) {
    $class = str_replace('_', '/', $class);
    $class = str_replace('\\', '/', $class);
	
	if (file_exists(RK_REDKURIPATH.'classes/'.strtolower($class).'.php')) {
        require_once(RK_REDKURIPATH . 'classes/' . strtolower($class) . '.php');
    }

    if (file_exists(RK_REDKURIPATH.'../../'.strtolower($class).'.php')) {
        require_once(RK_REDKURIPATH . '../../'.strtolower($class) . '.php');
    }
	if (file_exists(RK_PROTECTEDPATH.strtolower($class).'.php')) {
		require_once(RK_PROTECTEDPATH . strtolower($class) . '.php');
	}
}

function RKelapsedTime() {
	global $RKstart;
	
	return $elapsed = round(microtime(true) - $RKstart, 5) . '&#181;s';
}

