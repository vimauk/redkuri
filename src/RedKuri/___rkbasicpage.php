<?php

/*
 *   Copyright VIMA 2003 - 2011
 *   See accompanying licence file LICENCE.TXT or
 *   http://www.vima.co.uk/LICENCE.TXT
 */

namespace vima\RedKuri;

class rkBasicPage extends rkPage {
	protected $__heading;
	protected $__errors;
	
	function __construct($heading) {
		parent::__construct($heading);
//		$this->__errors = array();
		$this->setPageHeading($heading);
	}

	function setPageHeading($heading) {
		$this->__heading = $heading;
	}
	
	function setError($errorText) {
		$this->__errors[] = $errorText;
	}
	
	function renderError() {
		if (count($this->__errors) > 0) {
			$error = '<div class="alert">';
			$error .= '<a class="close" href="#">&times;</a>';
			$error .= '<p><strong>'.implode($this->__errors, '<br/>').'</strong></p>';
			$error .= '</div>';
			return $error;
		}
		return '';
	}
	
	function noErrors() {
		return count($this->__errors) == 0;
	}
	
	function isError() {
		return count($this->__errors > 0);
	}
	
	function renderPageStart() {}

	function renderPageEnd() {}
	
	function render() {
		parent::render();
	}

	function renderHTMLHeader() {
		$JAVASCRIPT = implode($this->__js);
		$CSS = implode($this->__css);
		
		echo <<<HTML
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
	<meta name="viewport" content="initial-scale:1.0, width=device-width" />
    <title>
HTML;
		echo $this->__title;
		echo <<<HTML
</title>

    <!--[if lt IE 9]>
      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <link href="/kale/smb/bootstrap/css/bootstrap.css" rel="stylesheet">

    <style type="text/css">
      /* Override some defaults 
      html, body {
        background-color: #eee;
      }
      body {
        padding-top: 40px; /* 40px to make the container go all the way to the bottom of the topbar */
      }
      .container > footer p {
        text-align: center; /* center align it with the container */
      }
      
	  @media screen and (min-width: 960px) {
		.container {
			zwidth:90%;
		}
	  }
	  
	  */

      /* The white background content wrapper 
      .container > .content {
        background-color: #fff;
        padding: 20px;
        margin: 0 -20px; /* negative indent the amount of the padding to maintain the grid system */
        -webkit-border-radius: 0 0 6px 6px;
           -moz-border-radius: 0 0 6px 6px;
                border-radius: 0 0 6px 6px;
        -webkit-box-shadow: 0 1px 2px rgba(0,0,0,.15);
           -moz-box-shadow: 0 1px 2px rgba(0,0,0,.15);
                box-shadow: 0 1px 2px rgba(0,0,0,.15);
      }

      /* Page header tweaks */
      .page-header {
        background-color: #fafafa;
        padding: 10px 20px 10px;
        margin: -20px -20px 10px -20px;
      }*/
    </style>

	<meta http-equiv="pragma" content="no-cache" />
	<meta http-equiv="imagetoolbar" content="no" />
	<meta http-equiv="content-type" content="text/html/PHP" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="robots" content="index,follow" />
	<meta name="rating" content="Safe For Kids" />
	<meta name="owner" content="" />
	<meta name="copyright" content="" />
    <meta name="description" content="">
	<meta name="author" content="" />
	<meta http-equiv="content-language" content="en-uk" />
    <title>$this->__title</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />$CSS$JAVASCRIPT
    <link rel="shortcut icon" href="images/favicon.ico">
    <link rel="apple-touch-icon" href="images/apple-touch-icon.png">
    <link rel="apple-touch-icon" sizes="72x72" href="images/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="114x114" href="images/apple-touch-icon-114x114.png">	
</head>
<body>
HTML;
	}

	function renderHTMLFooter() {
echo '	</body>
</html>';
	}

}