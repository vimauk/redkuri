<?php

namespace vima\RedKuri\Parts;

class CaptchaField extends Base {
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
