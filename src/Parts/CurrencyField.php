<?php

namespace vima\RedKuri\Parts;

class CurrencyField extends Base {
	function __construct($form, $name, $label='', $size)
	{
		parent::__construct($form, $name, $label, $size);
		$form->addErrorMessage($name.'error', '');
	}
	
	function render() {
		if ($this->visible) {
			$form = '<label for="'.$this->name.'">'.$this->label.'</label> ';
			$form .= '<div class="input-prepend">';
			$form .= '<span class="add-on">&pound;</span>';
			$form .= '<input type="textbox" name="'.$this->name.'" id="'.$this->name.'" value="'.$this->value.'" placeholder="'.$this->label.'"/>';
			$form .= '</div>';
		} else {
			if ($this->visible == 0) {
				$form = '<input type="hidden" name="'.$this->name.'" id="'.$this->name.'" value="'.$this->value.'"/>';
			}
		}
		
		return $form;
	}
}