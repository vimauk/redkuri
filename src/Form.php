<?php

namespace vima\RedKuri;

class Form extends Parts\Group {
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