<?php

namespace vima\RedKuri\Parts;

class Grid extends Base {
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

