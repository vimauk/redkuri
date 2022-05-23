<?php

namespace vima\RedKuri\Parts;

class NumericField extends Base
{
	function __construct($form, $name, $label='', $size) {
		parent::__construct($form, $name, $label, $size);
		$form->addErrorMessage($name.'error', '');
	}
	
	function render() {
		if ($this->visible) {
			$form = '<label for="'.$this->name.'">'.$this->label.'</label> ';
			$form .= '<div class="input-prepend">';
			$form .= '<input type="number" step="0.01" name="'.$this->name.'" id="'.$this->name.'" value="'.$this->value.'" placeholder="'.$this->label.'" />';
			$form .= '</div>';
		} else {
			if ($this->visible == 0) {
				$form = '<input type="hidden" name="'.$this->name.'" id="'.$this->name.'" value="'.$this->value.'"/>';
			}
		}
		
		return $form;
	}
}
