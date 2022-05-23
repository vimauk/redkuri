<?php

namespace vima\RedKuri\Parts;

class Base {
	protected $name;
	protected $form;
	protected $label;
	protected $placeholder;
	protected $value;
	protected $size;
	protected $visible;
	protected $classes;
	protected $error;
	
	function __construct($form, $name, $label='', $size='')
	{
		// Form could be a form or we could be adding to a group,
		// in which case I want the groups form (or the groups, groups form)
		
		while (get_class($form) == 'RKGroup') {
			$form = $form->form;
		}
		$this->form = $form;
		$this->name = $name;
		$this->label = $label;
		$this->size = $size;
		if ($form->page->Post($name) != null) $this->value = $form->page->Post($name);
		$this->classes  = array();
		$this->visible = true;
		$this->error = false;
	}
	
	function type()	{return $this->type;}
	function size()	{return $this->size;}
	function name() {return $this->name;}
	function visible() {return $this->visible === true;}
	function show() {$this->visible = true;}
	function hide() {$this->visible = false;}
	function totallyhide() {$this->visible = -1;}
	function label() {return $this->label;}
	function placeholder() {return $this->placeholder;}
	
	function setPlaceholder($placeholder) {
		$this->placeholder = $placeholder;
	}
	
	function setValue($value) {
		$this->value = $value;
	}

	function value() {
		return $this->value;
	}
	
	function integer() {
		return 0 + $this->value;
	}
	
	function string() {
        $string = htmlspecialchars($this->value);
        $string = trim($string);

        return $string;
	}

	function cleanString() {
		return trim('' . $this->value);
	}

	function parseDate($date='') {
		if ($date == '') $date  = $this->value;
		if (strlen($date) < 4) return false;
		
		$seperator = '';
		$r = array();
		$r['day'] = $r['month'] = $r['year'] = '';
		$months = array(
		  'jan','feb','mar','apr','may','jun','jul','aug','sep','oct','nov','dec'
		);

		// Only keep a single space between elements
		$date = preg_replace('/(\s)+/',' ',strtolower($date));

		// Find the seperator that is used
		if (strpos($date,'-') > 0) $seperator = '-';
		if (strpos($date,'/') > 0) $seperator = '/';

		// It could be a period, but check for use within time
		if (!$seperator && ($d = strpos($date,'.')) > 0) {
			$c = strpos($dt,':');
			if (!$c || ($c > $d)) $seperator = '.';
		}

		if ($seperator=='-' || $seperator=='/' || $seperator=='.') {
			$e = explode(' ',$date);
			$date = explode($seperator, $e[0]);
			if (!isset($e[1])) {
				$time = '00:00:00';
			} else {
				$time = $e[1];
			}
			$date[] = $time;
		} else {
			$date = explode(' ', $date, 4);
		}

		foreach ($date as $i => $v) $date[$i] = trim($v," \t\n\r\0\x0B,");
		
		$date[] = ''; // In case Time is missing
		$date[] = ''; // In case Time is missing
		$date[] = ''; // In case Time is missing
		
		@list($d1,$d2,$d3,$time) = $date;

		// get year
		if ($d1 > 1000) { $r['year'] = $d1; unset($date[0]); }
		if ($d3 > 1000) { $r['year'] = $d3; unset($date[2]); }

		// get month - defaults to mm-dd-yyyy

		// mmm dd yyyy
		if (!is_numeric($d1)) for ($i=0; $i<12; $i++) {
		  if (strstr($d1,$months[$i])!=false) {
			  $r['month'] = $i+1;
			  unset($date[0]);
			  break;
		  }
		}
		else if (!is_numeric($d2)) for($i=0; $i<12; $i++) {
		  if (strstr($d2,$months[$i])!=false) {
			  $r['month'] = $i+1;
			  unset($date[1]);
			  break;
		  }
		}
		else {
		  // yyyy-mm-dd
		  if ($d2 <= 12 && $d1 >= 1500) { $r['month'] = $d2; unset($date[1]); } else
		  // dd-mm-yyyy
		  if ($d2 <= 12 && $d3 >= 1500) { $r['month'] = $d2; unset($date[1]); } else
		  // mm-dd-yyyy
		  if ($d1 <= 12 && $d3 >= 1500) { $r['month'] = $d1; unset($date[0]); }
		}

		// get day
		unset($date[3]);
		$r['day'] = implode('',$date);
		if (!is_numeric($r['day'])||$r['day']> 31) return false;

		// get time
		$t = strtotime('1-jan-2012 '.$time);
		if($t) {
			$t = getdate($t);
            $r['hour'] = sprintf("%02d", $t['hours']);
            $r['minute'] = sprintf("%02d", $t['minutes']);
            $r['second'] = sprintf("%02d", $t['seconds']);
		} else {
			$t = strtotime('1-jan-2008 '.$time.':00');
			$t = getdate($t);
			$r['hour'] = sprintf("%02d", $t['hours']);
			$r['minute'] = sprintf("%02d", $t['minutes']);
			$r['second'] = sprintf("%02d", $t['seconds']);
		}
		return $r;
    }
	
	function niceDate()
	{
		$date = $this->parseDate($this->value);
		if (false === $date) return false;

		return $date['day'].'/'.$date['month'].'/'.$date['year'];
	}
	
	function date()
	{
		$date = $this->validdate();

		if (false === $date) return false;

		return $date['year'].'/'.$date['month'].'/'.$date['day'];
	}
	
	function datetime()
	{
		$date = $this->validdate();
		if (false === $date) return false;

		return $date['year'].'/'.$date['month'].'/'.$date['day'].' '.$date['hour'].':'.$date['minute'].':'.$date['second'];
	}
	
	function leapyear($year)
	{
		return date('L', strtotime("$year-01-01"));
	}
	
	function blankdate()
	{
		if ($this->value == '//') {
			return true;
		}
		return false;
	}
	
	function validdate()
	{
		$date = $this->parseDate($this->value);

		if ($date == false) return false;
		if ($date['year'] < 1000 or $date['year'] > 2050) return false;
		if ($date['month'] < 1 or $date['month'] > 12) return false;
		if ($date['day'] < 1) return false;
		switch ($date['month']) {
			case 2:
				if ($this->leapyear($date['year'])) {
					if ($date['day'] > 29) return false;
				} else {
					if ($date['day'] > 28) return false;
				}
				break;
			case 9:
			case 4:
			case 6:
			case 11:
				if ($date['day'] > 30) return false;
				break;
			default:
				if ($date['day'] > 31) return false;
				break;
		}
		return $date;
	}

	function validate()
	{
		return true;
	}
	
	function startup() {}
	
	function addClass($class)
	{
		$this->classes[] = $class;
	}
	
	function classes()
	{
		if (count($this->classes)) {
			return ' '.implode(' ', $this->classes);
		}
		return '';
	}
		
	function setError($errorText) {
		$this->error = true;
		$this->addClass('is-invalid');
		if ($this->form !== null) {
			$this->form->f($this->name.'error')->setMessage($errorText);
		}
	}

	function render()
	{
		$r = '';
		if ($this->visible) {
			$renderFunction = 'render'.str_replace(__NAMESPACE__ . '\\', '', get_class($this));
			$r .= $this->form->page->$renderFunction($this);
			if ($this->form->f($this->name.'-hidden') !== null) {
				$r .= $this->form->f($this->name.'-hidden')->render();
			}
		}
		return $r;
	}
}
