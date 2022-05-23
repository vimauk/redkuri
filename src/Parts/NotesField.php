<?php

namespace vima\RedKuri\Parts;

class NotesField extends Base
{
	function __construct($form, $name, $label='', $size=8) {
		parent::__construct($form, $name, $label, $size);
		$form->addErrorMessage($name.'error', '');
	}
	
	function xrender() {
		$form = '';
		if ($this->visible == true) {
			$form = '<label for="'.$this->name.'">'.$this->label.'</label>';
			$form .= '<textarea type="textbox" class="form-control textarea '.$this->classes().'" name="'.$this->name.'" id="'.$this->name.'" rows="'.$this->size.'"">'.$this->value.'</textarea>';
		}
		
		return $form;
	}
}
