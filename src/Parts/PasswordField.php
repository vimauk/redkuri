<?php

namespace vima\RedKuri\Parts;

class PasswordField extends Base
{
	function __construct($form, $name, $label='', $size) {
		parent::__construct($form, $name, $label, $size);
		$form->addErrorMessage($name.'error', '');
	}
}
