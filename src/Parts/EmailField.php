<?php

namespace vima\RedKuri\Parts;

class EmailField extends TextField
{
	function validate() {
		return isValidEmail($this->value);
		$form->addErrorMessage($name.'error', '');
	}
}
