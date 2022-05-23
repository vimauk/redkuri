<?php

namespace vima\RedKuri;

class RKPart {
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

class RKError extends RKPart
{
	protected $message;
	
	function __construct($form, $name, $message)
	{
		parent::__construct($form, $name, $message);
		
		$this->message = $message;
	}
	
	function setMessage($value) {
		$this->message = $value;
	}
	
	function message() {
		return $this->message;
	}
}

class RKText extends RKPart
{
	protected $text;
	
	function __construct($form, $name, $label) {
		parent::__construct($form, $name, $label);
		$form->addErrorMessage($name.'error', '');
        if ($form->page()->Post($name.'-hidden') != null) $text = \urldecode($form()->page->Post($name.'-hidden'));
		$this->setText('');
	}

	function setValue($text) {
	    $this->setText($text);
    }
	
	function render() {
		$r = '';
		if ($this->visible) {
			if (strlen($this->classes()) > 0) {
				$r .= '<span class="'.$this->classes().'">';
				$r .= $this->text;
				$r .= '</span>';
			} else {
				$r .= $this->text;
			}
		}
		$r .= $this->form->f($this->name.'-hidden')->render();
		return $r;
	}
	
	function setText($value) {
		$this->text = $value;
		$this->form->f($this->name.'-hidden')->setValue(\urlencode($value));
	}
}

class RKRichField extends RKTextField {
	function __construct($form, $name, $label='', $size) {
		parent::__construct($form, $name, $label, $size);
	}
	
	function render() {}
}

class RKPikadayField extends RKTextField {
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
class RKDateField extends RKTextField {
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

class RKHiddenField extends RKPart {
	function __construct($form, $name, $value)
	{
		parent::__construct($form, $name, '');
		$this->value = $value;
	}
	
	function render()
	{
		$form = '<input type="hidden" name="'.$this->name.'" id="'.$this->name.'" value="'.$this->value.'"/>';
		return $form;
	}
}

class RKUploadField extends RKPart
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

class RKNumericField extends RKPart
{
	function __construct($form, $name, $label='', $size) {
		parent::__construct($form, $name, $label, $size);
		$form->addErrorMessage($name.'error', '');
	}
	
	function render() {
		if ($this->visible) {
			$form = '<label for="'.$this->name.'">'.$this->label.'</label> ';
			$form .= '<div class="input-prepend">';
			$form .= '<input type="number" step="0.01" name="'.$this->name.'" id="'.$this->name.'" value="'.$this->value.'" placeholder="'.$this->label.'" />';
			$form .= '</div>';
		} else {
			if ($this->visible == 0) {
				$form = '<input type="hidden" name="'.$this->name.'" id="'.$this->name.'" value="'.$this->value.'"/>';
			}
		}
		
		return $form;
	}
}

class RKCurrencyField extends RKPart {
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

class RKTextField extends RKPart {
	protected $type;
	
	function __construct($form, $name, $label='', $size) {
		$this->type = '';
		parent::__construct($form, $name, $label, $size);
		$form->addErrorMessage($name.'error', '');
	}
	
	function setType($type) {
		$this->type = $type;
	}
}

class RKPasswordField extends RKPart
{
	function __construct($form, $name, $label='', $size) {
		parent::__construct($form, $name, $label, $size);
		$form->addErrorMessage($name.'error', '');
	}
}

class RKCaptchaField extends RKPart {
	function __construct($form, $name)
	{
		parent::__construct($form, $name);
		$form->addErrorMessage($name.'error', '');
	}
	
	function render() {
		$form = '';
		if ($this->visible) {
			require_once(RK_REDKURIPATH.'libraries/recaptcha-php-1.11/recaptchalib.php');
			$form .= '<script type="text/javascript">
				var RecaptchaOptions = {
					theme : \'clean\'
				};</script>';
			$form .= recaptcha_get_html("6LfmV74SAAAAAARcT7gqqKLGvIzKH94OygY-eZGA");
		}
		
		return $form;
	}
	
	function validate()
	{
		require_once(RK_REDKURIPATH.'libraries/recaptcha-php-1.11/recaptchalib.php');
		$privatekey = "6LfmV74SAAAAACnLqVvUq-YwbTnQGIpl1gU1SP7-";
		$resp = recaptcha_check_answer ($privatekey,
			$_SERVER["REMOTE_ADDR"],
			$this->form->page()->Post("recaptcha_challenge_field"),
			$this->form->page()->Post("recaptcha_response_field"));
		if (!$resp->is_valid) {
			return false;
		} else {
			return true;
		}
  }
}

class RKEmailField extends RKTextField
{
	function validate() {
		return isValidEmail($this->value);
		$form->addErrorMessage($name.'error', '');
	}
}

class RKWysiwygAdvancedField extends RKPart
{
	function __construct($form, $name, $label='', $size=8) {
		parent::__construct($form, $name, $label, $size);
		$form->addErrorMessage($name.'error', '');
	}
	
	function render() {
		$form = '';
		if ($this->visible == true) {
			$form = '<label for="'.$this->name.'">'.$this->label.'</label>';
			$form .= '<div class="'.$this->classes().'" id="'.$this->name.'-wysiwyg"></div>';
			$form .= '<textarea type="text" name="'.$this->name.'" id="'.$this->name.'" style="display:none;">'.$this->value.'</textarea>';
		}
		
		return $form;
	}
}

class RKWysiwygField extends RKPart
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

class RKNotesField extends RKPart
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

class RKWeightField extends RKChoiceField {
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


class RKHeightField extends RKChoiceField {
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

class RKChoiceField extends RKPart {
	protected $options;
	protected $values;
	protected $default;
	
	function options() {
		return $this->options;
	}
	
	function values() {
		return $this->values;
	}

	function __construct($form, $name, $label='', $options, $values='', $default='') {
		$this->value = $default;
		parent::__construct($form, $name, $label);
		$form->addErrorMessage($name.'error', '');
		$this->options = $options;
		$this->values = $values;
	}

	function xrender() {
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

class RKCheckboxField extends RKPart {
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

class RKButton extends RKPart {
	protected $clicked;
	protected $default;
	protected $display;
	protected $colour;
	
	function __construct($form, $name, $default = false, $display = '') {
		$this->value = $display == '' ? $name : $display;
		$this->default = $default;
		$this->clicked = false;
		
		parent::__construct($form, $name);
		
		if ($display == '') {
			$this->display = $name;
		} else {
			$this->display = $display;
		}
	}
	
	function isDefault() {
		return $this->default === true;
	}
	
	function colour($colour) {
		$this->colour = $colour;
	}
	
	function startup() {
		if ($this->name() == $this->value()) {
			$this->clicked = true;
			$cleanname = str_ireplace('-', '', $this->name());
			if (method_exists($this->form, 'event_'.$cleanname.'_onClick')) {
				$method = 'event_'.$cleanname.'_onClick';
				$this->form->$method();
			}
		}
	}
	
	function alert() {
		$this->addClass('btn-danger');
		return $this;
	}

	function success() {
		$this->setClass('btn-success');
		return $this;
	}

	function isClicked() {
		return $this->clicked;
	}
}


class RKGrid extends RKPart{
	protected $table;
	protected $fields;
	protected $where;
	protected $sortorder;
	protected $sortcolumn;
	protected $groupby;
	protected $showcheckboxes;
	protected $primary;

	function __construct($form, $name, $label, $definition, $table, $fields, $where, $primary='') {
		parent::__construct($form, $name);
		$this->clicked = false;
		$this->label = $label;
		$this->definition = $definition;
		$this->table = $table;
		$this->fields = $fields;
		$this->where = $where;
		$this->showCheckboxes = false;
		$this->primary = $primary;
	}

	function showCheckboxes() {
		$this->showCheckboxes = true;
	}
	
	function render() {
		$db = new EyeMySQLAdap(RK_DB_HOST, RK_DB_USERNAME, RK_DB_PASSWORD, RK_DB_DATABASE);

		$x = new EyeDataGrid($db, $this->name, RK_BASEPATH.'img/');
		$x->setQuery($this->fields, $this->table, $this->primary, $this->where);

		if (EyeDataGrid::isAjaxUsed()) {
			$x->printTable();
			exit;
		}

		$fields = explode('|', $this->definition);
		foreach ($fields as $field) {
			$options = explode('*', $field);
			if (!isset($options[1])) $options[1] = 'T';
			switch ($options[1]) {
				case 'E':
					$x->addStandardControl(EyeDataGrid::STDCTRL_EDIT, $options[2]);
					break;
				case 'X':
					$x->addStandardControl(EyeDataGrid::STDCTRL_DELETE, $options[2]);
					break;
				case 'S':
					$x->setColumnType($options[0], EyeDataGrid::TYPE_STRIPPED);
					break;
				case 'C': // Currency
					//$x->setColumnType($options[0], EyeDataGrid::TYPE_DOLLAR);
					if (isset($options[2]) && $options[2] <> '') {
						$x->setColumnHeader($options[0], $options[2]);
					}
					$x->setColumnAlignment($options[0], 'right');
					break;
				case 'L': // Link
					$x->setColumnType($options[0], EyeDataGrid::TYPE_HREF, $options[2]); //'http://google.com/search?q=%FirstName%'); // Google Me!
					if (isset($options[3]) && $options[3] <> '') {
						$x->setColumnHeader($options[0], $options[3]);
					}
					break;
				case 'P': // Percent
					$x->setColumnType($options[0], EyeDataGrid::TYPE_PERCENT, false, array('Back' => '#c3daf9', 'Fore' => 'black'));
					if (isset($options[2]) && $options[2] <> '') {
						$x->setColumnHeader($options[0], $options[2]);
					}
					break;
				case 'D': // Date
					$setting = '';
					if (isset($options[2]))
						$setting = trim($options[2]);
					if ($setting == '') $setting = 'j-M-Y';
					$x->setColumnType($options[0], EyeDataGrid::TYPE_DATE, $setting, true); // Change the date format
					if (isset($options[3]) && $options[3] <> '') {
						$x->setColumnHeader($options[0], $options[3]);
					}
					break;
				case 'H': // Column Header
					$x->hideColumn($options[0]);
					break;
				case 'HH': // Hide Header
					$x->hideHeader();
					break;
				case 'T': // Text Field
					if (isset($options[2]) && $options[2] <> '') {
						$x->setColumnHeader($options[0], $options[2]);
					}
					break;
				case 'S': // Sort
					$x->setOrder($options[0], EyeDataGrid::ORDER_DESC);
					break;
			}
		}
		if ($this->showCheckboxes) {
			$x->showCheckboxes();
		}
/*		$x->setColumnHeader('FirstName', 'First Name');
		$x->setColumnType('createdtime', EyeDataGrid::TYPE_DATE, 'j-M-Y', true); // Change the date format
		$x->setColumnType('updatedtime', EyeDataGrid::TYPE_DATE, 'j-M-Y', true); // Change the date format
		$x->setColumnType('tombstonetime', EyeDataGrid::TYPE_DATE, 'j-M-Y', true); // Change the date format
		$x->setColumnType('tombstone', EyeDataGrid::TYPE_ARRAY, array('0' => '', '1' => 'DELETED')); // Convert db values to something better
		$x->setColumnType('id', EyeDataGrid::TYPE_PERCENT, false, array('Back' => '#c3daf9', 'Fore' => 'black'));*/
		//$x->allowFilters();
		//$x->hideFooter();
		if (isset($this->sortorder)) {
			$x->setOrder($this->name.$this->sortcolumn, $this->sortorder);
		}
		$x->setResultsPerPage(10);
		if (isset($this->groupby)) {
			$x->setGroupby($this->groupby);
		}
		return $x->printTable();
	}
	
	function setOrder($column, $order) {
		$this->sortcolumn = $column;
		$this->sortorder = $order;
	}
	
	function setGroupby($column) {
		$this->groupby = $column;
	}
}

class RKGroup extends RKPart {
	protected $parts;
	
	function __construct($form, $name)
	{
		parent::__construct($form, $name);
	}
	
	function name() {return $this->name;}

	function visible() {return $this->visible;}
	
	function show()
	{
		$this->visible = true;
		foreach ($this->parts as $part) {
			$part->show();
		}
	}
	
	function hide()
	{
		$this->visible = false;
		foreach ($this->parts as $part) {
			$part->hide();
		}
	}
	
	function startup()
	{
		if (!isset($this->parts)) return;
		foreach ($this->parts as $part) {
			$part->startup();
		}
	}
	
	function render()
	{
		if (!is_array($this->parts) OR count($this->parts) < 1) return $this->template;
		
		foreach ($this->parts as $part) {
			if (get_class($part) == 'RKGroup') {
				if ($part->visible()) {
					$this->template = preg_replace('/({'.$part->name().'})(.*)({\/'.$part->name().'})/si', '$2', $this->template);
					$part->render();
				} else {
					$this->template = preg_replace('/({'.$part->name().'}.*{\/'.$part->name().'})/si', '', $this->template);
					$part->render();
				}
			} else {
				if ($part->visible()) {
					$this->template = preg_replace('/({'.$part->name().'})/', $part->render(), $this->template);
				} else {
					$this->template .= $part->render();
				}
			}
		}
		return $this->template;
	}
	
	function addGroup($name, $visible=true) {
		$this->parts[$name] = new RKGroup($this, $name, $visible);
		return $this->parts[$name];
	}
	
	function addErrorMessage($name, $text) {
		$this->parts[$name] = new RKError($this, $name, $text);
		return $this->parts[$name];
	}

	function addText($name, $text='', $size='') {
        $this->parts[$name.'-hidden'] = new RKHiddenField($this, $name.'-hidden', $text);
		$this->parts[$name] = new RKText($this, $name, $text);
		return $this->parts[$name];
	}

	function addUpload($name, $text, $size='') {
		$this->parts[$name] = new RKUploadField($this, $name, $text);
		return $this->parts[$name];
	}
	
	function addCaptchaField($name) {
		$this->parts[$name] = new RKCaptchaField($this, $name);
		return $this->parts[$name];
	}
	
	function addTextField($name, $label='', $size='') {
		return $this->parts[$name] = new RKTextField($this, $name, $label, $size);
	}
	
	function addDateField($name, $label='', $size='') {
		$this->parts[$name] = new RKDateField($this, $name, $label, $this->size());
	}

	function addPikadayField($name, $label='', $size='') {
		$this->parts[$name] = new RKPikadayField($this, $name, $label, $this->size());
	}

	function addCurrencyField($name, $label='', $size='') {
		$this->parts[$name] = new RKCurrencyField($this, $name, $label, $this->size());
	}

	function addNumericField($name, $label='', $size='') {
		$this->parts[$name] = new RKNumericField($this, $name, $label, $this->size());
	}
	
	function addHiddenField($name, $value='') {
		$this->parts[$name] = new RKHiddenField($this, $name, $value);
		return $this->parts[$name];
	}

	function addEmailField($name, $label='', $size='') {
		$this->parts[$name] = new RKEmailField($this, $name, $label, $this->size());	
		return $this->parts[$name];
	}

	function addWeightField($name, $default='', $type='KG') {
		$this->parts[$name] = new RKWeightField($this, $name, $default, $type);
		return $this->parts[$name];
	}

	function addHeightField($name, $default='') {
		$this->parts[$name] = new RKHeightField($this, $name, $default);	
		return $this->parts[$name];
	}

	function addChoiceField($name, $label='', $options, $default='', $values='') {
		$this->parts[$name] = new RKChoiceField($this, $name, $label, $options, $values, $default);	
		return $this->parts[$name];
	}

	function addCheckboxField($name, $label='', $options=null, $default='', $values='') {
		$this->parts[$name] = new RKCheckboxField($this, $name, $label, $options, $values, $default);
		return $this->parts[$name];
	}

	function addPasswordField($name, $label='', $size='') {
		$this->parts[$name] = new RKPasswordField($this, $name, $label, $this->size());
		return $this->parts[$name];
	}

	function addNotesField($name, $label='', $size=10) {
		$this->parts[$name] = new RKNotesField($this, $name, $label, $size);
		return $this->parts[$name];
	}

	function addWysiwygField($name, $label='', $size=10) {
		$this->parts[$name] = new RKWysiwygField($this, $name, $label, $size);
		return $this->parts[$name];
	}
	
	function addGridField($name, $label='', $definition, $table, $field, $where, $primary='') {
		$this->parts[$name] = new RKGrid($this, $name, $label, $definition, $table, $field, $where, $primary);
		return $this->parts[$name];
	}
	
	function addButton($name, $default = false, $label='') {
		$this->parts[$name] = new RKButton($this, $name, $default, $label);
		return $this->parts[$name];
	}

	function addUploadField($name, $default = false, $label='') {
		$this->parts[$name] = new RKUploadField($this, $name, $default, $label);
		return $this->parts[$name];
	}
	
	function field($name) {
		if (isset($this->parts[$name])) {
			return $this->parts[$name];
		}
		//redkuri_error(9999, '<i>'.$name.'</i> field not found in <i>'.$this->name().'</i>');
	}
	
	function f($name) {return $this->field($name); }
}


class Form extends RKGroup {
	public $template;
	protected $submitted;
	protected $page;
	protected $layout = '';
	protected $errors = 0;
	protected $enctype = 'multipart/form-data'; //'application/x-www-form-urlencoded';

	function __construct($page) {
		$this->page = $page;
		$this->submitted = false;
		$this->errors = 0;

		if ($page->Post('RKFormSubmit') == 'submitted') {
			$this->submitted = true;
		} else {
		}
		$this->startup();

		if (!$this->isSubmitted()) {
			$this->event_created();
		}
		$this->main();
	}
	
    function event_created() {}

	function first() {}
	
	function inline() {
		$this->layout = 'form-inline';
	}
	
	function horizontal() {
		$this->layout = 'form-horizontal';
	}
		
	function isSubmitted() {
		return $this->submitted;
	}
		
	function render() {
		$form = '<form method="post" enctype="'.$this->enctype.'" id="page_form"';
		if ($this->layout <> '') $form .= ' class="'.$this->layout.'"';
		$form .= '>';
		$form .= '<input type="submit" name="RKDefaultButton" style="display:none;" value="submitted"/>';
		$form .= '<input type="hidden" name="RKFormSubmit" value="submitted"/>';
		$form .= parent::render();
		$form .= '</form>';
		return $form;
	}
	

	function addError($errorText)
	{
		$this->errors[] = $errorText;
	}
		
	function noErrors()
	{
		return count($this->errors) === 0;
	}
	
	function isError()
	{
		return count($this->errors) > 0;
	}
	
	function page() {
		return $this->page;
	}
	
	function main() {}
	
	function event_onSubmitForm() {}
	
	function renderTextField($name, $value, $placeholder, $type, $visible)
	{
		if ($visible) {
			$form = '<input type="text" class="form-control" name="'.$name.'" id="'.$name.'" value="'.$value.'" placeholder="'.$placeholder.'"';
			if ($type == 'number' OR $type == 'tel' OR $type == 'email') {
				$form .= ' type="'.$type.'"';
			}
			$form .= '>';
		} else {
			$form = '<input type="hidden" name="'.$name.'" id="'.$name.'" value="'.$value.'">';
		}		
		return $form;
	}
}