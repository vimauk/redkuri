<?php

namespace vima\RedKuri\Parts;

class Button extends Base {
	protected $clicked;
	protected $default;
	protected $display;
	protected $colour;
	
	function __construct($form, $name, $default = false, $display = '') {
		$this->value = $display == '' ? $name : $display;
		$this->default = $default;
		$this->clicked = false;
		
		parent::__construct($form, $name);
		
		if ($display == '') {
			$this->display = $name;
		} else {
			$this->display = $display;
		}
	}
	
	function isDefault() {
		return $this->default === true;
	}
	
	function colour($colour) {
		$this->colour = $colour;
	}
	
	function startup() {
		if ($this->name() == $this->value()) {
			$this->clicked = true;
			$cleanname = str_ireplace('-', '', $this->name());
			if (method_exists($this->form, 'event_'.$cleanname.'_onClick')) {
				$method = 'event_'.$cleanname.'_onClick';
				$this->form->$method();
			}
		}
	}
	
	function alert() {
		$this->addClass('btn-danger');
		return $this;
	}

	function success() {
		$this->setClass('btn-success');
		return $this;
	}

	function isClicked() {
		return $this->clicked;
	}
	
	function display() {
		return $this->display;
	}
}
