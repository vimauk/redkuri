<?php

namespace vima\RedKuri\Parts;

class TextField extends Base {
	protected $type;
	
	function __construct($form, $name, $label='', $size) {
		$this->type = '';
		parent::__construct($form, $name, $label, $size);
		$form->addErrorMessage($name.'error', '');
	}
	
	function setType($type) {
		$this->type = $type;
	}
}
