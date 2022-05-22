<?php

namespace RedKuri;

class Utility {
	static public function String($var) {
		if ($var == null) return null;
		return trim($var);
	}

	static public function Integer($var) {
		if ($var == null) return null;
		$var = round(0+$var);
		return $var;
	}

	static public function checkInteger($value, $nulls=false, $min=false, $max=false) {
		if ($value == null && $nulls == true) return true;
		if (!is_numeric($value)) return false;
		if ($value != round($value)) return false;

		if ($min !== false) {
			if ($value < $min) return false;
		}
		if ($max !== false) {
			if ($value > $max) return false;
		}
		return true;
	}

	static public function Float($var) {
		if ($var == null) return null;
		if (!is_numeric($var)) return null;
		return $var;
	}

	static public function checkFloat($value, $nulls=false, $min=false, $max=false) {
		if ($value == null && $nulls == true) return true;
		if (!is_numeric($value)) return false;

		if ($min !== false) {
			if ($value < $min) return false;
		}
		if ($max !== false) {
			if ($value > $max) return false;
		}
		return true;
	}

	static public function Date($var) {
		if ($var == null) return null;
		return $var;
	}

	static public function Get($var, $default = null) {
		if (isset($_GET[$var])) {
			return $_GET[$var];
		} else {
			return $default;
		}
	}

	static public function Post($var, $default = null) {
		if (isset($_POST[$var])) {
			return $_POST[$var];
		}
		return $default;
	}

	static public function Server($var) {
		if (isset($_SERVER[$var])) {
			return $_SERVER[$var];
		} else {
			return null;
		}
	}

	static public function Session($var, $value = null) {
		if ($value !== null) {
			$_SESSION[$var] = $value;
		}
		if (isset($_SESSION[$var])) {
			return $_SESSION[$var];
		}
		return null;
	}

	static public function rkThrowError($error) {
		echo '<div style="font-family:arial;color:#333;background:#ffa;width:100%;font-size:9pt;padding:10px;position:static;top:0px;left:0px;right:0px;height:50px;">';
		echo '<b>An error occured</b> - '.$error;
		echo '</div>';
		die();
	}
	
	static public function Files($var) {
		if (isset($_FILES[$var])) {
			return $_FILES[$var];
		} else {
			return '';
		}
	}

	private static $startTime;

	public static function startup()
	{
		self::$startTime = microtime(true);
	}

	public static function executionTime()
	{
		return round(microtime(true) - this::startTime, 5) . '&#181;s';
	}
}

/*
 *
 *   E-mail Functions
 *
 */
 
function sendEmail($from, $to, $subject, $body, $priority='') {
	if (!isValidEmail($from) or !isValidEmail($to)) return false;

	require_once(RK_REKURIPATH.'libraries/htmlMimeMail5/htmlMimeMail5.php');

	$mail = new htmlMimeMail5();
	$mail->setFrom($from);
	$mail->setSubject($subject);
	if ($priority != '') $mail->setPriority($priority);
	$mail->setHTML($body);
	$text = $body;
	$mail->setText($text);
	//$mail->addEmbeddedImage(new fileEmbeddedImage('background.gif'));
	//$mail->addAttachment(new fileAttachment('example.zip'));
	if (!is_array($to)) {
		$to = array($to);
	}
	$mail->send($to);
}

function isValidEmail($email) {
		// trim() the entered E-Mail
		$str_trimmed = trim($email);
		// find the @ position
		$at_pos = strrpos($str_trimmed, "@");
		// find the . position
		$dot_pos = strrpos($str_trimmed, ".");
		// this will cut the local part and return it in $local_part
		$local_part = substr($str_trimmed, 0, $at_pos);
		// this will cut the domain part and return it in $domain_part
		$domain_part = substr($str_trimmed, $at_pos);
		if(!isset($str_trimmed) || is_null($str_trimmed) || empty($str_trimmed) || $str_trimmed == "") {
			return false;
		}
		elseif(!valid_local_part($local_part)) {
			return false;
		}
		elseif(!valid_domain_part($domain_part)) {
			return false;
		}
		elseif($at_pos > $dot_pos) {
			return false;
		}
		elseif(!valid_local_part($local_part)) {
			return false;
		}
		elseif(($str_trimmed[$at_pos + 1]) == ".") {
			return false;
		}
		elseif(!preg_match("/[(@)]/", $str_trimmed) || !preg_match("/[(.)]/", $str_trimmed)) {
			return false;
		}
		else {
			return true;
		}


}
function valid_dot_pos($email) {
	$str_len = strlen($email);
	for($i=0; $i<$str_len; $i++) {
		$current_element = $email[$i];
		if($current_element == "." && ($email[$i+1] == ".")) {
			return false;
			break;
		}
		else {

		}
	}
	return true;
}
function valid_local_part($local_part) {
	if(preg_match("/[^a-zA-Z0-9-_@.!#$%&'*\/+=?^`{\|}~]/", $local_part)) {
		return false;
	}
	else {
		return true;
	}
}

function valid_domain_part($domain_part) {
	if(preg_match("/[^a-zA-Z0-9@#\[\].]/", $domain_part)) {
		return false;
	}
	elseif(preg_match("/[@]/", $domain_part) && preg_match("/[#]/", $domain_part)) {
		return false;
	}
	elseif(preg_match("/[\[]/", $domain_part) || preg_match("/[\]]/", $domain_part)) {
		$dot_pos = strrpos($domain_part, ".");
		if(($dot_pos < strrpos($domain_part, "]")) || (strrpos($domain_part, "]") < strrpos($domain_part, "["))) {
			return true;
		}
		elseif(preg_match("/[^0-9.]/", $domain_part)) {
			return false;
		}
		else {
			return false;
		}
	}
	else {
		return true;
	}
}

// End of e-mail functions

function redirect($url, $die = true) {
	if(headers_sent()) {
		echo '<script type="text/javascript">location.href="'.$url.'";</script>';
	} else {
		header('Location: '.$url);
	}
	if ($die) {
		die;
	}
}

class rkLanguage
{
	static protected $plural = array(
		'/(tory)$/i' => "$1",
		'/(quiz)$/i' => "$1zes",
		'/^(ox)$/i' => "$1en",
		'/([m|l])ouse$/i' => "$1ice",
		'/(matr|vert|ind)ix|ex$/i' => "$1ices",
		'/(x|ch|ss|sh)$/i' => "$1es",
		'/([^aeiouy]|qu)y$/i' => "$1ies",
		'/(hive)$/i' => "$1s",
		'/(?:([^f])fe|([lr])f)$/i' => "$1$2ves",
		'/(shea|lea|loa|thie)f$/i' => "$1ves",
		'/sis$/i' => "ses",
		'/([ti])um$/i' => "$1a",
		'/(tomat|potat|ech|her|vet)o$/i' => "$1oes",
		'/(bu)s$/i' => "$1ses",
		'/(alias)$/i' => "$1es",
		'/(octop)us$/i' => "$1i",
		'/(ax|test)is$/i' => "$1es",
		'/(us)$/i' => "$1es",
		'/s$/i' => "s",
		'/$/' => "s"
	);

	static protected $singular = array(
		'/(quiz)zes$/i' => "$1",
		'/(matr)ices$/i' => "$1ix",
		'/(vert|ind)ices$/i' => "$1ex",
		'/^(ox)en$/i' => "$1",
		'/(alias)es$/i' => "$1",
		'/(octop|vir)i$/i' => "$1us",
		'/(cris|ax|test)es$/i' => "$1is",
		'/(shoe)s$/i' => "$1",
		'/(o)es$/i' => "$1",
		'/(bus)es$/i' => "$1",
		'/([m|l])ice$/i' => "$1ouse",
		'/(x|ch|ss|sh)es$/i' => "$1",
		'/(m)ovies$/i' => "$1ovie",
		'/(s)eries$/i' => "$1eries",
		'/([^aeiouy]|qu)ies$/i' => "$1y",
		'/([lr])ves$/i' => "$1f",
		'/(tive)s$/i' => "$1",
		'/(hive)s$/i' => "$1",
		'/(li|wi|kni)ves$/i' => "$1fe",
		'/(shea|loa|lea|thie)ves$/i' => "$1f",
		'/(^analy)ses$/i' => "$1sis",
		'/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => "$1$2sis",
		'/([ti])a$/i' => "$1um",
		'/(n)ews$/i' => "$1ews",
		'/(h|bl)ouses$/i' => "$1ouse",
		'/(corpse)s$/i' => "$1",
		'/(us)es$/i' => "$1",
		'/s$/i' => ""
	);

	static protected $irregular = array(
		'move' => 'moves',
		'foot' => 'feet',
		'goose' => 'geese',
		'sex' => 'sexes',
		'child' => 'children',
		'man' => 'men',
		'tooth' => 'teeth',
		'person' => 'people'
	);

	static protected $uncountable = array(
		'sheep',
		'fish',
		'deer',
		'series',
		'species',
		'money',
		'rice',
		'information',
		'equipment'
	);

	public static function pluralise($string)
	{

		if (in_array(strtolower($string), rkLanguage::$uncountable)) {
			return $string;
		}

		foreach (rkLanguage::$irregular as $pattern => $result) {
			$pattern = '/' . $pattern . '$/i';

			if (preg_match($pattern, $string))
				return preg_replace($pattern, $result, $string);
		}

		foreach (rkLanguage::$plural as $pattern => $result) {
			if (preg_match($pattern, $string))
				return preg_replace($pattern, $result, $string);
		}

		return $string;
	}

	public static function singularise($string)
	{

		if (in_array(strtolower($string), rkLanguage::$uncountable))
			return $string;

		foreach (rkLanguage::$irregular as $result => $pattern) {
			$pattern = '/' . $pattern . '$/i';

			if (preg_match($pattern, $string))
				return preg_replace($pattern, $result, $string);
		}

		foreach (rkLanguage::$singular as $pattern => $result) {
			if (preg_match($pattern, $string))
				return preg_replace($pattern, $result, $string);
		}

		return $string;
	}
    public static function paddedNumber($number, $len)
    {
        return substr(str_repeat('0', $len) . $number, -$len);
    }

    public static function hyphenSplit($string, $chunksize=4) {
        $r = '';
        for ($i=0; $i<strlen($string); $i++) {
            if ($i % $chunksize == 0) $r .= '-';
            $r .= $string[$i];
        }
        return $r;
    }

    public static function strrot($s, $n = 13) {
        static $letters = 'AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz';
        $n = (int)$n % 26;
        if (!$n) return $s;
        if ($n < 0) $n += 26;
        if ($n == 13) return str_rot13($s);
        $rep = substr($letters, $n * 2) . substr($letters, 0, $n * 2);
        return strtr($s, $letters, $rep);
    }

    public static function obfuscate($text, $key) {
        $outText = '';
        for($i=0;$i<strlen($text);$i++) {
            $outText .= $text[$i] ^ $key[$i % strlen($key)];
        }

        return $outText;
    }

    public static function fullStub($brand, $name, $id) {
        $stub = '';
        if (strlen($brand) > 0) {
            $stub = sysCore::getUrlFriendly($brand) . '/';
        }

        $stub .= sysCore::getUrlFriendly($name).'-'.Utility::encodeID($id);

        return $stub;
    }

    public static function decodeStub($stub) {
        $stub = urldecode($stub);
        $dash = strripos($stub, '-');
        if ($dash === false) return false;
        $stub = substr($stub, $dash+1);
        $id = Utility::decodeID($stub);
        return $id;
    }

    public static function decodeID($id) {
        $id = substr($id, -9);
        $id = Utility::paddedNumber(Utility::encodeDecodeID($id, true, 9), 14);
        $id = strrev($id);
        return intval($id);
    }

    public static function encodeID($id) {
        $id = (string)$id;
        $id = Utility::paddedNumber(rand(0,9999), 4).Utility::paddedNumber($id, 14);
        $id = substr($id, -14);
        $id = strrev($id);

        return Utility::encodeDecodeID($id, false, 9);
    }

    public static function encodeDecodeID($in, $to_num = false, $pad_up = false, $pass_key = null) {
        $index = 'bcdfghjklmnpqrstvwxyz0123456789BCDFGHJKLMNPQRSTVWXYZ';
        $base  = strlen($index);

        if ($to_num) {
            // Digital number  <<--  alphabet letter code
            $out = 0;
            $len = strlen($in) - 1;

            for ($t = $len; $t >= 0; $t--) {
                $bcp = bcpow($base, $len - $t);
                $out = $out + strpos($index, substr($in, $t, 1)) * $bcp;
            }

            if (is_numeric($pad_up)) {
                $pad_up--;

                if ($pad_up > 0) {
                    $out -= pow($base, $pad_up);
                }
            }
        } else {
            // Digital number  -->>  alphabet letter code
            $out   = '';
            if (is_numeric($pad_up)) {
                $pad_up--;

                if ($pad_up > 0) {
                    $in += pow($base, $pad_up);
                }
            }

            for ($t = ($in != 0 ? floor(log($in, $base)) : 0); $t >= 0; $t--) {
                $bcp = bcpow($base, $t);
                $a   = floor($in / $bcp) % $base;
                $out = $out . substr($index, $a, 1);
                $in  = $in - ($a * $bcp);
            }
        }
        return $out;
    }

	public static function pluralise_if($count, $string)
	{
		if ($count == 1)
			return "1 $string";
		else
			return $count . " " . rkLanguage::pluralise($string);
	}

    public static function sanitiseString($string) {
        $string = trim(htmlspecialchars($string));

        return $string;
    }

    public static function cleanText($text, $options=array()) {

        /* Fix incorrectly spaced commas */

        $text = preg_replace('/ ,/i', ', ', $text);
        $text = preg_replace('/,([a-z])/i', ', $1', $text);

        $text = preg_replace('/[^(\x20-\x7F)]*/','', $text);

        /* Replace non-standard quotes and hyphens */

        $search = array(chr(145),
            chr(146),
            chr(147),
            chr(148),
            chr(151));

        $replace = array("'",
            "'",
            '"',
            '"',
            '-');

        $text = str_replace($search, $replace, $text);

        return $text;
    }
}

function sqlnow($justdate = false) {
	if ($justdate)
		return date('Y-m-d');
	return date('Y-m-d H:i:s');
}

function dateToMySQL($date) {
	$split = preg_split('(/|-|\\|\.)', $date);
	if (count($split) != 3) return null;

	list($day, $month, $year) = $split;
	$date = "$year-$month-$day";

	return $date;
}

function mySQLToDate($date) {
	$split = preg_split('( )', $date);
	if (count($split) == 2) {
		$date = $split[0];
	}
	$split = preg_split('(/|-|\\|\.)', $date);
	if (count($split) != 3) return null;

	list($year, $month, $day) = $split;
	$date = "$day/$month/$year";

	return $date;
}

function pd($what) {
	echo '<pre style="font-family:sans-serif;color:#222;background:#fafafa;font-size:9pt;padding:4px 4px;position:absolute;z-index:10000">';
	var_dump($what);
	echo '</pre>';
}

function p($what) {
	echo '<pre style="font-family:sans-serif;color:#222;background:#fafafa;font-size:9pt;padding:4px 4px;position:absolute;z-index:10000">';
	echo $what.'<br>';
	echo '</pre>';
}

function smartShorten($string, $length) {
	$length = $length * 4;
	$chars = array(
		'A' => 3,
		'B' => 3,
		'C' => 3,
		'D' => 3,
		'E' => 3,
		'F' => 3,
		'G' => 3,
		'H' => 3,
		'I' => 2,
		'J' => 3,
		'K' => 3,
		'L' => 3,
		'M' => 3,
		'N' => 3,
		'O' => 3,
		'P' => 3,
		'Q' => 3,
		'R' => 3,
		'S' => 3,
		'T' => 3,
		'U' => 3,
		'V' => 3,
		'W' => 4,
		'X' => 3,
		'Y' => 3,
		'Z' => 3,
		'a' => 2,
		'b' => 2,
		'c' => 2,
		'd' => 2,
		'e' => 2,
		'f' => 2,
		'g' => 2,
		'h' => 2,
		'i' => 1,
		'j' => 2,
		'k' => 2,
		'l' => 1,
		'm' => 3,
		'n' => 2,
		'o' => 2,
		'p' => 2,
		'q' => 2,
		'r' => 2,
		's' => 2,
		't' => 2,
		'u' => 2,
		'v' => 2,
		'w' => 2,
		'x' => 2,
		'y' => 2,
		'z' => 2
	);

	$short = '';
	$len = 0;
	$pos = 0;

	while (($len < $length) AND ($pos < strlen($string))) {
		$short .= $string[$pos];

		if (array_key_exists($string[$pos], $chars) === TRUE) {
			$len += $chars[$string[$pos++]];
		} else {
			$len += 2;
			$pos++;
		}
	}

	return $short;
}
