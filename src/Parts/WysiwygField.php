<?php

namespace vima\RedKuri\Parts;

class WysiwygField extends Base
{
	function __construct($form, $name, $label='', $size=8) {
		parent::__construct($form, $name, $label, $size);
		$form->addErrorMessage($name.'error', '');
	}
	
	function render() {
		$form = '';
		if ($this->visible == true) {
			$form = '<label for="'.$this->name.'">'.$this->label.'</label>';
			$form .= '<div class="form-control content wysiwyg'.$this->classes().'" id="'.$this->name.'-wysiwyg"></div>';
			$form .= '<textarea type="text" name="'.$this->name.'" id="'.$this->name.'" style="display:none;">'.$this->value.'</textarea>';
		}
		
		return $form;
	}
}
