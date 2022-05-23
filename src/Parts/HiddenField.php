<?php

namespace vima\RedKuri\Parts;

class HiddenField extends Base {
	function __construct($form, $name, $value)
	{
		parent::__construct($form, $name, '');
		$this->value = $value;
	}
	
	function render()
	{
		$form = '<input type="hidden" name="'.$this->name.'" id="'.$this->name.'" value="'.$this->value.'"/>';
		return $form;
	}
}
