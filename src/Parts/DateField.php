<?php

namespace vima\RedKuri\Parts;

class DateField extends TextField {
	function __construct($form, $name, $label='', $size) {
		parent::__construct($form, $name, $label, $size);
		$form->addErrorMessage($name.'error', '');
		$dd = $this->form->page()->Post($this->name.'dd');
		if ($dd < 1 or $dd > 31) $dd = '';
		$mm = $this->form->page()->Post($this->name.'mm');
		if ($mm < 1 or $mm > 12) $mm = '';
		$yyyy = $this->form->page()->Post($this->name.'yyyy');
		if (!is_numeric($yyyy)) $yyyy = '';
		$this->value = $yyyy.'/'.$mm.'/'.$dd;
	}
	
	function render() {
		$parts = explode('/', $this->value);
		if (count($parts) != 3) {
			$parts = explode('-', $this->value);
			if (count($parts) != 3) {
				$parts[0] = $parts[1] = $parts[2] = '';
			}
		}
		if ($this->visible) {
			$form = '<div class="row">';
			$form .= '<div class="col-3">';
			$form .= '<input type="text" name="'.$this->name.'dd" id="'.$this->name.'dd" value="'.$parts[2].'" placeholder="DD">';
			$form .= '</div>';
			$form .= '<div class="col-3">';
			$form .= '<input type="text" name="'.$this->name.'mm" id="'.$this->name.'mm" value="'.$parts[1].'" placeholder="MM">';
			$form .= '</div>';
			$form .= '<div class="col-6">';
			$form .= '<input type="text" name="'.$this->name.'yyyy" id="'.$this->name.'yyyy" value="'.$parts[0].'" placeholder="YYYY">';
			$form .= '</div>';
			$form .= '</div>';
		} else {
			if ($this->visible == 0) {
				$form = '<input type="hidden" name="'.$this->name.'" id="'.$this->name.'" value="'.$this->value.'">';
			}
		}		
		return $form;
	}
}

