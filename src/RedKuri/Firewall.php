<?php

namespace RedKuri;

class Firewall {
    protected $IPHeader;

    function __construct() {
        $this->IPHeader = 'REMOTE_ADDR';
    }

    function usingCloudflare() {
        $this->IPHeader = 'HTTP_CF_CONNECTING_IP';
    }

    function usingBlazingfast() {
        $this->IPHeader = 'X-Real-IP';
    }

    function considerBan($severity=1) {
    }

    function isBanned($ip=null) {
        return false;
    }

    function getIP() {
        $IP = '';
        if (getenv('HTTP_CLIENT_IP')) {
            $IP =getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
            $IP =getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('HTTP_X_FORWARDED')) {
            $IP =getenv('HTTP_X_FORWARDED');
        } elseif (getenv('HTTP_FORWARDED_FOR')) {
            $IP =getenv('HTTP_FORWARDED_FOR');
        } elseif (getenv('HTTP_FORWARDED')) {
            $IP = getenv('HTTP_FORWARDED');
        } else {
            if (isset($_SERVER[$this->IPHeader])) {
				$IP = $_SERVER[$this->IPHeader];
			}
        }
        return $IP;
    }

    function block($type, $found, $location) {
        $url  = ( !empty( $_SERVER['HTTPS'] ) ) ? "https://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] : "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];

        $block = array();

        $block['datetime'] = date( "M j G:i:s Y" );
        $block['fromip'] = $this->getIP();
        $block['url'] = $url;
        $block['uri'] = Utility::Server('REQUEST_URI');
        $block['string'] = Utility::Server('QUERY_STRING');
        $block['method'] = Utility::Server('REQUEST_METHOD');
        $block['useragent'] = Utility::Server('HTTP_USER_AGENT');
        $block['referrer'] = Utility::Server('HTTP_REFERER');
        $block['type'] = $type;
        $block['found'] = $found;
        $block['location'] = $location;

        if ($block['type'] != 'IP Banned') {
            $this->considerBan($block['fromip'], AT_FIREWALL_BAN_SEVERITY, $block['type'].' '.Utility::Server('REQUEST_URI'));
        }

        unset($_SESSION['RK-IP']);
        unset($_SESSION);
        @session_destroy();
        @session_start();

        return $block;
    }

    function protect() {
        if ($this->isBanned()) {
            return $this->block( 'IP Banned', '', '' );
        }

        if (!$this->cookieCheck()) {
            return $this->block( 'Cookie Check', '', '' );
        }

        /* Method Blacklist*/
        if ( preg_match( "/^(TRACE|DELETE|TRACK)/i", Utility::Server('REQUEST_METHOD'), $matched ) ) {
            return $this->block( 'Method Blacklist', $matched[1], 'Method' );
        }

        /* Referrer */
        if ( isset($_SERVER['HTTP_REFERER']) && preg_match( "/<[^>]*(".$this->XSS().")[^>]*>/i", Utility::Server('HTTP_REFERER'), $matched ) ) {
            return $this->block( 'Referrer XSS', $matched[1], 'Referrer' );
        }

        /* User Agent Empty */
        /*if ( preg_match( "/(^$)/i", $_SERVER['HTTP_USER_AGENT'], $matched ) ) {
            return $this->block( 'User Agent Empty', $matched[1], 'UserAgent Blank' );
        }*/

        /* User Agent Blacklist */
        //if ( preg_match( "/^(".$this->UserAgent().").*/i", $_SERVER['HTTP_USER_AGENT'], $matched ) ) {
        //    return $this->block( 'User Agent Blacklist', $matched[1], 'UserAgent' );
        //}

        /* Query - > 750 */
        if ( isset($_SERVER['QUERY_STRING']) && strlen( $_SERVER['QUERY_STRING'] ) > 750 ) {
            return $this->block( 'Query Too Long', '> 750', 'Query Too Long' );
        }

        /* Query - Cross Site Scripting */
        if ( preg_match( "/(<|<.)[^>]*(".$this->XSS().")[^>]*>/i", Utility::Server('QUERY_STRING'), $matched ) ) {
            return $this->block( 'Query XSS', $matched[1], 'Query' );
        }

        if ( preg_match( "/((\%3c)|(\%3c).)[^(\%3e)]*(".$this->XSS().")[^(\%3e)]*(%3e)/i", Utility::Server('QUERY_STRING'), $matched ) ) {
            return $this->block( 'Query XSS', $matched[1], 'Query' );
        }

        /* Query - traversal */
        if ( preg_match( "/^.*(".$this->Traversal().").*/i", Utility::Server('QUERY_STRING'), $matched ) ) {
            return $this->block( 'Query traversal', $matched[1], 'Query' );
        }

        /* Query - Remote File Inclusion */
        if ( preg_match( "/^.*(".$this->RFI().").*/i", Utility::Server('QUERY_STRING'), $matched ) ) {
//            return $this->block( 'Query RFI', $matched[1], 'Query' );
        }

        /* Query - SQL injection */
        if ( preg_match( "/^.*(".$this->SQL().").*/i", Utility::Server('QUERY_STRING'), $matched ) ) {
            return $this->block( 'Query SQL', $matched[1], 'Query' );
        }

        return true;
    }

    // Check Anti-Cookie steal trick.

    function cookieCheck() {
        if (isset($_SESSION)) {
            if (isset($_SESSION['rkusername'])) {
                if (!(isset($_SESSION['RK-IP']))) {
                    $_SESSION['RK-IP'] = $this->getIP();
                    return true;
                } else {
                    if (!($_SESSION['RK-IP'] == $this->getIP())) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    function XSS() {
        $xss = "javascript|vbscript|expression|applet|meta|xml|blink|";
        $xss .= "link|style|script|embed|object|iframe|frame|frameset|";
        $xss .= "ilayer|layer|bgsound|title|base|form|img|body|href|div|cdata";
        // onerror(), document.cookie, alert, widnow. String.fromCharCode(, onmouseover=, <BODY onload, style, svg onload
        return $xss;
    }

    function UserAgent() {
        $ua  = "curl|wget|winhttp|HTTrack|clshttp|loader|email|harvest|extract|grab|miner|";
        $ua .= "libwww-perl|acunetix|sqlmap|python|nikto|scan|bot";
        return $ua;
    }

    function SQL() {
        $sql = "[\x22\x27](\s)*(or|and)(\s).*(\s)*\x3d|";
        $sql .= "cmd=ls|cmd%3Dls|";
        $sql .= "(drop|alter|create|truncate).*(index|table|database)|";
        $sql .= "insert(\s).*(into|member.|value.)|";
        $sql .= "(select|union|order).*(select|union|order)|";
//        $sql .= "0x[0-9a-f][0-9a-f]|";
        $sql .= "benchmark\([0-9]+,[a-z]+|benchmark\%28+[0-9]+%2c[a-z]+|";
        $sql .= "eval\(.*\(.*|eval%28.*%28.*|";
        $sql .= "update.*set.*=|delete.*from";
        return $sql;
    }

    function traversal() {
        $traversal = "\.\.\/|\.\.\\|%2e%2e%2f|%2e%2e\/|\.\.%2f|%2e%2e%5c";
        return $traversal;
    }

    function RFI() {
        $rfi  = "%00|";
        $rfi .= "(?:((?:ht|f)tp(?:s?)|file|webdav)\:\/\/|~\/).*\.\w{2,3}|"; //|\/
        $rfi .= "(?:((?:ht|f)tp(?:s?)|file|webdav)%3a%2f%2f|%7e%2f%2f).*\.\w{2,3}";
        return $rfi;
    }
}
