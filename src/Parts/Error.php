<?php

namespace vima\RedKuri\Parts;


class Error extends Base
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
	
	function renderError() {}
}
