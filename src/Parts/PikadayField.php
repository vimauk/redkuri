<?php

namespace vima\RedKuri\Parts;

class PikadayField extends TextField {
	function render() {
		$parts = explode('/', $this->value);
		if (count($parts) != 3) {
			$parts = explode('-', $this->value);
			if (count($parts) != 3) {
				$parts[0] = $parts[1] = $parts[2] = '';
			}
		}
		if ($this->visible) {
			$form = '
			<div class="input-group field has-addons">
				<input type="text" name="'.$this->name.'" id="'.$this->name.'" value="'.$this->value.'" class="form-control input" placeholder="DD/MM/YYYY">
                <a href="javascript:void(null);" id="'.$this->name.'icon" class="button btn btn-secondary pikadayicon input-group-text"></a>
			</div>';
			//<i class="fa fa-calendar"></i>
		} else {
			if ($this->visible == 0) {
				$form = '<input type="hidden" name="'.$this->name.'" id="'.$this->name.'" value="'.$this->value.'">';
			}
		}
		return $form;
	}
}
