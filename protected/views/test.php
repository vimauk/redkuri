<?php

use RedKuri\Core;
use RedKuri\Utility;
use RedKuri as RK;

define ('BR', "<br>\n");

echo Core::version().BR;
echo Utility::Server('SCRIPT_NAME').BR;

$firewall = new RK\Firewall();
$check = $firewall->protect();

if ($check !== true) {
	die('Blocked');
}

echo $firewall->getIP().BR;

function testDB() {
	static $__db;

	if (!($__db)) {
		$__db = new RK\Database(RK_DB_HOST, RK_DB_DATABASE, RK_DB_USERNAME, RK_DB_PASSWORD, 'mysql');
	}
	return $__db;
}

$creator = new RK\DBCreator(testDB());

$creator->database('test');
$creator->table('testobjects')
	->base()
	->string('description')
	->endtable();
$creator->go();
	

class testObject extends RK\DBObject {
	protected $description;

	function __construct($param = NULL) {
		$this->__db = testDB();
		if (!is_null($param) AND !is_numeric($param)) {
			$param = trim($param);
			$res = testDB()->query('SELECT id FROM testOjbect WHERE name=?', array($param));
			if ($res->numberRows() == 0) {
				return $this;
			} else {
				$param = $res->f('id');
			}
		}
		parent::__construct($param);
		return $this;
	}
}

$testObject = new testObject();
$testObject->setName('1');
$testObject->setDescription('One');
$testObject->save();

$secondTestObject = new testObject();
$secondTestObject->setName('2');
$secondTestObject->setDescription('Two');
$secondTestObject->save();

$testObject2 = new testObject('1');

class thisForm extends RK\Form {
	function startup() {
		$this->template = <<<EOD
			<h2>Test Form Template</h2>
			{error}
			{texterror}{text}
			{noteserror}{notes}
			{passworderror}{password}
			{choiceerror}{choice}
			<div class="mt-3">
			{button} {button2}
			</div>
EOD;
		
		$this->addErrorMessage('error', '');
		
		$this->addTextField('text', 'Text Field');
		$this->f('text')->setPlaceholder('Test Text');
		
		$this->addNotesField('notes', '');
		
		$this->addPasswordField('password', 'Password Label');
		
		$this->addChoiceField('choice', 'Choice Label', array('Yes', 'No'));
		
		$this->addButton('button', true, 'Button');
		$this->addButton('button2', false, 'Secondary');

		$this->setError('Startup');
		
		parent::startup();
	}
	
	function event_button_onClick() {
		if ($this->f('text')->value() != '4') {
			$this->f('text')->setError('Wrong Value');
		}
		if ($this->f('choice')->value() === 'No') {
			$this->f('choice')->setError('No!');
		}
		if (strlen($this->f('password')->value()) < 8) {
			$this->f('password')->setError('Minimum of 8 characters');
		}
	}
}

class testPage extends RK\BootstrapPage {
// class testPage extends RK\BulmaPage {
	protected $form;
	protected $stage;
	
	function __construct($title) {
		parent::__construct($title);
	}

	function startup() {
		parent::startup();
		$this->form = new thisForm($this);
	}
	
	function partContent() {
		$form = $this->form->render();
		
		return <<<EOD
<h1>This is a Test Page</h1>
	$form
EOD;
	}
}

$page = new testPage('RedKuri Test');
$form = new RK\Form($page);
$page->render();