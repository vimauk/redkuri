<?php

namespace vima\RedKuri;

class TailwindPage extends HTMLPage {
	protected $__content;
	protected $__title;
	protected $__css;
	protected $__script;
	protected $__meta;

	function __construct($title='') {
		parent::__construct($title);
		$this->startup();
	}	

	function htmlHeader() {
		$TITLE = $this->__title;
		$CSS = implode("\n", $this->__css);
		$RK_BASEPATH = RK_BASEPATH;

		echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="keywords" content="fabtrackr">
    <meta name="description" content="fabtracker">
    <meta name="author" content="Anthony Fearn">
	<meta name="owner" content="Anthony Fearn" />
	<meta name="copyright" content="Copyright (c) 2010-2021 Anthony Fearn" />
	<meta name="mobile-web-app-capable" content="yes" />
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta name="apple-mobile-web-app-status-bar-style" content="black" />

    <link href="{$RK_BASEPATH}/css/style.css" rel="stylesheet">
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
<body class="h-screen bg-orange-400 dark:bg-slate-900 text-white mx-auto p-2">
HTML;
	}

	function renderError($part) {
		if ($part->message()) {
			$error = '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
			$error .= '<strong>'.implode($part->message(), '<br/>').$part->message().'</strong>';
			$error .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
			$error .= '</div>';

			return $error;
		}
		return '';
	}

	function renderLabel($part) {
		if (strlen($part->label()) > 0) {
			return '<label class="form-label" for="'.$part->name().'">'.$part->label().'</label>';
		}
		return '';
	}
	
	function renderTextField($part) {
		if ($part->visible()) {
			$form = $this->renderLabel($part);
			$form .= '<input type="text" class="form-control '.$part->classes().'" name="'.$part->name().'" id="'.$part->name().'" value="'.$part->value().'" placeholder="'.$part->placeholder().'" size="'.$part->size().'"';
			if ($part->type() == 'number' OR $part->type() == 'tel' OR $part->type() == 'email') {
				$form .= ' type="'.$part->type().'"';
			}
			$form .= '>';
		} else {
			if ($part->visible() == 0) {
				$form = '<input type="hidden" name="'.$part->name().'" id="'.$part->name().'" value="'.$part->value().'">';
			}
		}
		
		return $form;
	}

	function renderButton($part) {
		if ($part->isDefault() == true) {
			return '<button type="submit" class="btn btn-primary'.$part->classes().'" name="'.$part->name().'" id="'.$part->name().'" value="'.$part->name().'">'.$part->display().'</button>';
		} else {
			return '<button type="submit" class="btn btn-secondary'.$part->classes().'" name="'.$part->name().'" id="'.$part->name().'" value="'.$part->name().'">'.$part->display().'</button';
		}
	}

	function renderPasswordField($part) {
		$form = '';
		if ($part->visible() == true) {
			$form = $this->renderLabel($part);
			$form .= '<input type="password" class="form-control'.$part->classes().'" name="'.$part->name().'" id="'.$part->name().'" value="'.$part->value().'"/>';
		}
		
		return $form;
	}

	function renderNotesField($part) {
		$form = '';

		if ($this->visible == true) {
			$form .= $this->renderLabel($part);
			$form .= '<textarea class="form-control'.$part->classes().'" name="'.$part->name().'" id="'.$part->name().'" rows="'.$part->size().'">'.$part->value().'</textarea>';
		}
		
		return $form;
	}

	function renderChoiceField($part) {
		$form = '';

		$form .= $this->renderLabel($part);
		
		$form .= '<select class="form-select'.$part->classes().'" name="'.$part->name().'" id="'.$part->name().'">';
		
		for ($i=0; $i<count($part->options()); $i++) {
			if (is_array($part->values())) {
				$form .= '<option value="'.$part->values()[$i].'"';
				if ($part->values[$i] == $part->value()) {
					$form .= ' selected';
				}
			} else {
				$form .= '<option value="'.$part->options()[$i].'"';
				if ($part->options()[$i] == $part->value()) {
					$form .= ' selected';
				}
			}
			$form .= '>'.$part->options()[$i].'</option>';
		}
		$form .= '</select>';

		return $form;
	}
}