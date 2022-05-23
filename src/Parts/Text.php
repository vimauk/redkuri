<?php

namespace vima\RedKuri\Parts;

class Text extends Base
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
