<?php

namespace vima\RedKuri\Parts;

function kgToStone($kg) {
	$stone = kgToStones($kg);
	$pounds = kgToStonePounds($kg);

	if ($stone > 0)
		return "{$stone}<small>st</small>{$pounds}<small>lb</small>";

	return "{$pounds}<small>lb</small>";
}

function kgToPounds($kg) {
	$pounds = $kg / 0.45359237;

	return $pounds;
}

function kgToStones($kg) {
	$pounds = kgToPounds($kg);
	$stone = floor($pounds / 14);

	return $stone;
}

function kgToStonePounds($kg) {
	$stones = kgToStones($kg);
	$pounds = kgToPounds($kg);
	$pounds -= $stones * 14;
	$pounds = floor($pounds);
	return $pounds;
}

class WeightField extends ChoiceField {
	function __construct($form, $name, $default='', $type='KG') {
		switch ($type) {
			case 'Pounds':
			case 'Stones':
			case 'Stone':
			case 'Imperial':
			case 'I':
				$i = 0;
				for($s=6; $s<26; $s++) {
					for ($p=0; $p<14; $p++) {
						$pounds = ($s*14)+$p;
						$kg = round($pounds * 0.45359237, 1);
						$options[$i] = "$s St $p lb ($pounds lbs) - $kg Kg";
						$values[$i++] = $kg;
					}
				}
				break;
			default:
				$i = 0;
				for ($kg=38; $kg<165; $kg+=0.1) {
					$kg = round($kg, 1);
					$options[$i] = "$kg Kg (".kgToStone($kg).')';
					$values[$i++] = $kg;
				}

		}
		parent::__construct($form, $name, '', $options, $values, $default);
		$form->addErrorMessage($name.'error', '');
	}
}

