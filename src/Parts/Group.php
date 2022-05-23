<?php

namespace vima\RedKuri\Parts;

class Group extends Base {
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
		$this->parts[$name] = new Group($this, $name, $visible);
		return $this->parts[$name];
	}
	
	function addErrorMessage($name, $text) {
		$this->parts[$name] = new Error($this, $name, $text);
		return $this->parts[$name];
	}

	function addText($name, $text='', $size='') {
        $this->parts[$name.'-hidden'] = new HiddenField($this, $name.'-hidden', $text);
		$this->parts[$name] = new Text($this, $name, $text);
		return $this->parts[$name];
	}

	function addUpload($name, $text, $size='') {
		$this->parts[$name] = new UploadField($this, $name, $text);
		return $this->parts[$name];
	}
	
	function addCaptchaField($name) {
		$this->parts[$name] = new CaptchaField($this, $name);
		return $this->parts[$name];
	}
	
	function addTextField($name, $label='', $size='') {
		return $this->parts[$name] = new TextField($this, $name, $label, $size);
	}
	
	function addDateField($name, $label='', $size='') {
		$this->parts[$name] = new DateField($this, $name, $label, $this->size());
	}

	function addPikadayField($name, $label='', $size='') {
		$this->parts[$name] = new PikadayField($this, $name, $label, $this->size());
	}

	function addCurrencyField($name, $label='', $size='') {
		$this->parts[$name] = new CurrencyField($this, $name, $label, $this->size());
	}

	function addNumericField($name, $label='', $size='') {
		$this->parts[$name] = new NumericField($this, $name, $label, $this->size());
	}
	
	function addHiddenField($name, $value='') {
		$this->parts[$name] = new HiddenField($this, $name, $value);
		return $this->parts[$name];
	}

	function addEmailField($name, $label='', $size='') {
		$this->parts[$name] = new EmailField($this, $name, $label, $this->size());	
		return $this->parts[$name];
	}

	function addWeightField($name, $default='', $type='KG') {
		$this->parts[$name] = new WeightField($this, $name, $default, $type);
		return $this->parts[$name];
	}

	function addHeightField($name, $default='') {
		$this->parts[$name] = new HeightField($this, $name, $default);	
		return $this->parts[$name];
	}

	function addChoiceField($name, $label='', $options, $default='', $values='') {
		$this->parts[$name] = new ChoiceField($this, $name, $label, $options, $values, $default);	
		return $this->parts[$name];
	}

	function addCheckboxField($name, $label='', $options=null, $default='', $values='') {
		$this->parts[$name] = new CheckboxField($this, $name, $label, $options, $values, $default);
		return $this->parts[$name];
	}

	function addPasswordField($name, $label='', $size='') {
		$this->parts[$name] = new PasswordField($this, $name, $label, $this->size());
		return $this->parts[$name];
	}

	function addNotesField($name, $label='', $size=10) {
		$this->parts[$name] = new NotesField($this, $name, $label, $size);
		return $this->parts[$name];
	}

	function addWysiwygField($name, $label='', $size=10) {
		$this->parts[$name] = new WysiwygField($this, $name, $label, $size);
		return $this->parts[$name];
	}
	
	function addGridField($name, $label='', $definition, $table, $field, $where, $primary='') {
		$this->parts[$name] = new Grid($this, $name, $label, $definition, $table, $field, $where, $primary);
		return $this->parts[$name];
	}
	
	function addButton($name, $default = false, $label='') {
		$this->parts[$name] = new Button($this, $name, $default, $label);
		return $this->parts[$name];
	}

	function addUploadField($name, $default = false, $label='') {
		$this->parts[$name] = new UploadField($this, $name, $default, $label);
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

