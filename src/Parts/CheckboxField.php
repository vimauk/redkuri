<?php

namespace vima\RedKuri\Parts;

class CheckboxField extends Base {
	protected $options;
	protected $default;

	function __construct($form, $name, $label='', $options, $values='', $default='') {
		$this->value = $default;
		parent::__construct($form, $name, $label);
		$form->addErrorMessage($name.'error', '');
		$this->options = $options;
		$this->values = $values;
	}

	function render() {
		$form = '';
		if ($this->visible == true) {
			$form .= '<label for="'.$this->name.'">'.$this->label.'</label> ';
			$form .= '<select class="form-control" name="'.$this->name.'" id="'.$this->name.'">';
			for ($i=0; $i<count($this->options); $i++) {
				if (is_array($this->values)) {
					$form .= '<option value="'.$this->values[$i].'"';
					if ($this->values[$i] == $this->value) {
						$form .= ' selected';
					}
				} else {
					$form .= '<option value="'.$this->options[$i].'"';
					if ($this->options[$i] == $this->value) {
						$form .= ' selected';
					}
				}
				$form .= '>'.$this->options[$i].'</option>';
			}
			$form .= '</select>';
		}
		return $form;
	}

	function startup() {
		if ($this->form->page()->Post($this->name())) {
			$this->value = $this->form->page()->Post($this->name());
		}
	}
	function setValue($value) {
		$this->value = $value;
	}
}