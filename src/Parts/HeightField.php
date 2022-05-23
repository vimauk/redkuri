<?php

namespace vima\RedKuri\Parts;

class HeightField extends ChoiceField {
	function __construct($form, $name, $default='') {
		$j = 0;
		for($f=4; $f<7; $f++) {
			for ($i=0; $i<12; $i++) {
				$inches = ($f*12) + $i;
				$m = round($inches / 39.3700787, 2);
				$options[$j] = "$f ft $i\" - $m m";
				$values[$j++] = $m;
			}
		}
		parent::__construct($form, $name, '', $options, $values, $default);
		$form->addErrorMessage($name.'error', '');
	}
}
