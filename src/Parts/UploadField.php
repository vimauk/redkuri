<?php

namespace vima\RedKuri\Parts;

class UploadField extends Base
{
	function __construct($form, $name, $label='') {
		parent::__construct($form, $name, $label);
		$this->addClass('form-control-upload');
		$form->addErrorMessage($name.'error', '');
	}
	
	function temporaryFile()
	{
		if (isset($_FILES[$this->name]))
			return $_FILES[$this->name]['tmp_name'][0];
	}
	
	function filename()
	{
		if (isset($_FILES[$this->name]))
			return $_FILES[$this->name]['name'][0];
	}

    function size()
    {
        if (isset($_FILES[$this->name]))
            return $_FILES[$this->name]['size'][0];
    }

	function type()
	{
		if (isset($_FILES[$this->name]))
			return $_FILES[$this->name]['type'][0];
	}
	
	function render() {
		if ($this->visible) {
			$form = '<label for="'.$this->name.'">'.$this->label.'</label> ';
			$form .= '<input type="file" class="file-input '.$this->classes().'" name="'.$this->name.'[]" id="'.$this->name.'"/>';
		}
		
		return $form;
	}
}
