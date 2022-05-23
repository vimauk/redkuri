<?php

namespace vima\RedKuri;

class Resultset {
	protected $__resultset;
	protected $__fields;
	protected $__classname;

	function __construct($resultSet) {
		$this->__resultset = $resultSet;
		$this->nextRow();
	}

	function f($fieldName) {
		if (isset($this->__fields[$fieldName])) {
			return $this->__fields[$fieldName];
		}
		return NULL;
	}

	function setClassname($class) {
		$this->__classname = $class;
	}

	function getObject($classname = null) {
		if ($classname == null) {
			$object = new $this->__classname;
		} else {
			$object = new $classname;
		}
		foreach ($this->__fields as $name => $value) {
			$call = 'set'.$name;
			$object->$call($value);
		}

		return $object;
	}

	function getArray() {
		return $this->__resultset->fetchAll();
	}

	function nextRow() {
		if ($this->__resultset != false) {
			$this->__fields = $this->__resultset->fetch(\PDO::FETCH_ASSOC);
			if ($this->__fields == false) {
				return false;
			}
			return true;
		}
		return false;
	}

	function numRows() {
		if ($this->__resultset) {
			return $this->__resultset->rowCount();
		}
		return false;
	}

	function numberRows() {
		return $this->numRows();
	}

	function hasRows() {
		return $this->numRows() > 0;
	}
}