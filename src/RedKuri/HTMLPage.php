<?php

namespace RedKuri;

class HTMLPage extends Page {
	protected $__content;
	protected $__title;
	protected $__css;
	protected $__script;
	protected $__meta;

	function __construct($title='') {
		$this->setTitle($title);
		$this->__css = array();
		$this->__script = array();
		$this->startup();
		$this->__content = '{content}';
	}

	function setTitle($title) {
		$this->__title = $title;
	}
	
	function startup() {}
	
	function render() {
		$this->preRender();
		$this->htmlHeader();
		$this->renderHTMLHeader();
		$this->pageHeader();
		$this->renderPageStart();
		$this->pageContent();
		$this->renderPageContent();
		$this->pageFooter();
		$this->renderPageEnd();
		$this->htmlFooter();
		$this->renderHTMLFooter();
		$this->postRender();
	}

	function preRender() {}

	function htmlHeader() {
		$TITLE = $this->__title;
		$CSS = implode("\n", $this->__css);
		$RK_BASEPATH = RK_BASEPATH;

		echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
	<link href='https://fonts.googleapis.com/css?family=Roboto:400,100,300|Reenie+Beanie' rel='stylesheet' type='text/css'>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
    <meta name="keywords" content="fabtrackr">
    <meta name="description" content="fabtracker">
    <meta name="author" content="Anthony Fearn">
	<meta name="owner" content="Anthony Fearn" />
	<meta name="copyright" content="Copyright (c) 2010-2021 Anthony Fearn" />
	<meta name="mobile-web-app-capable" content="yes" />
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta name="apple-mobile-web-app-status-bar-style" content="black" />

	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <!-- link href="{$RK_BASEPATH}css/bootstrap.min.css" rel="stylesheet" -->
    <link href="{$RK_BASEPATH}/css/theme.min.css" rel="stylesheet">
    <link href="{$RK_BASEPATH}/css/style.css" rel="stylesheet">$CSS


    <link rel="shortcut icon" href="{$RK_BASEPATH}favicon.ico">
	<link rel="icon" sizes="196x196" href="{$RK_BASEPATH}img/icon196.png">
	<link rel="icon" sizes="128x128" href="{$RK_BASEPATH}img/icon128.png">
	<link rel="apple-touch-icon" sizes="128x128" href="{$RK_BASEPATH}img/icon128.png">
	<link rel="apple-touch-icon-precomposed" sizes="128x128" href="{$RK_BASEPATH}img/icon128.png">
    <link rel="apple-touch-icon-precomposed" sizes="144x144" href="{$RK_BASEPATH}img/apple-touch-icon-144-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="114x114" href="{$RK_BASEPATH}img/apple-touch-icon-114-precomposed.png">
    <link rel="apple-touch-icon-precomposed" sizes="72x72" href="{$RK_BASEPATH}img/apple-touch-icon-72-precomposed.png">
    <link rel="apple-touch-icon-precomposed" href="{$RK_BASEPATH}img/apple-touch-icon-57-precomposed.png">
	<link rel="apple-touch-startup-image" href="{$RK_BASEPATH}img/startup-320x460.png" sizes="320x460"  media="(max-device-width: 480px) and not (-webkit-min-device-pixel-ratio: 2)" />
	<link rel="apple-touch-startup-image" href="{$RK_BASEPATH}img/startup-640x920.png" sizes="640x920"  media="(max-device-width: 480px) and (-webkit-min-device-pixel-ratio: 2)" />
	<link rel="apple-touch-startup-image" href="{$RK_BASEPATH}img/startup-768x1004.png" sizes="768x1004" media="screen and (min-device-width: 481px) and (max-device-width: 1024px) and (orientation:portrait)" />
	<link rel="apple-touch-startup-image" href="{$RK_BASEPATH}img/startup-768x1004.png" sizes="1024x748" media="screen and (min-device-width: 481px) and (max-device-width: 1024px) and (orientation:landscape)" />
	<link rel="apple-touch-startup-image" href="{$RK_BASEPATH}img/startup-320x460.png" />

    <script src="https://kit.fontawesome.com/a6e6e2c4b7.js" crossorigin="anonymous"></script>

    <title>$TITLE</title>

	$CSS
</head>
<body>
HTML;
	}

	function renderHTMLHeader() {}
	function pageHeader() {}
	function renderPageStart() {}
	function pageContent() {}
	function pageFooter() {}
	function renderPageEnd() {}
	function renderHTMLFooter() {}
	function htmlFooter() {}
	function postRender() {}

	function addMeta($meta)
	{
		$this->meta[] = $meta;
	}

	function addJavaScript($script) {
		if ((substr($script, 0, 8) != '<script ') &&
			(substr($script, 0, 4) != '<!--')){
			$script = '<script type="text/javascript" language="javascript" src="'.$script.'"></script>';
		}

		if (array_search($script, $this->__script) === false) {
			$this->__script[] = $script;
		} else {
			$this->setError('Already present '.htmlspecialchars($script));
		}
	}

	function addCSS($css, $push=false) {
		if (substr($css, 0, 6) != '<link ' AND substr($css, 0, 4) != '<!--') {
			$css = '<link rel="stylesheet" href="'.$css.'" type="text/css" media="" />';
		}
		if (array_search($css, $this->__css) === false) {
            if ($push) {
                array_unshift($this->__css, $css);
            } else {
                $this->__css[] = $css;
            }
		} else {
			$this->setError('Already present '.htmlspecialchars($css));
		}
	}

	function renderPart($matches) {
		return $this->$matches[0]();
	}
	
	function renderPageContent() {
		preg_match_all('/{([a-z]*)}/',  $this->__content, $matches);
		foreach($matches[1] as $part) {
			$function = 'part'.$part;
			if (method_exists($this, $function)) {
				$this->__content = preg_replace('/({'.$part.'})/', $this->$function(), $this->__content);
			}
		}
		echo $this->__content;
	}
}