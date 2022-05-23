<?php

namespace vima\RedKuri;

class Language {
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

		if (in_array(strtolower($string), self::$uncountable)) {
			return $string;
		}

		foreach (self::$irregular as $pattern => $result) {
			$pattern = '/' . $pattern . '$/i';

			if (preg_match($pattern, $string))
				return preg_replace($pattern, $result, $string);
		}

		foreach (self::$plural as $pattern => $result) {
			if (preg_match($pattern, $string))
				return preg_replace($pattern, $result, $string);
		}

		return $string;
	}

	public static function singularise($string)
	{

		if (in_array(strtolower($string), self::$uncountable))
			return $string;

		foreach (self::$irregular as $result => $pattern) {
			$pattern = '/' . $pattern . '$/i';

			if (preg_match($pattern, $string))
				return preg_replace($pattern, $result, $string);
		}

		foreach (self::$singular as $pattern => $result) {
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
			return $count . " " . self::pluralise($string);
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
