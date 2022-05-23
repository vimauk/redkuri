<?php
namespace vima\RedKuri;

class DBCreator {
	protected $db;
	protected $tables;
	protected $keys;
	protected $currenttable;

	function __construct($db) {
		$this->db = $db;
		$this->tables = $this->keys = '';
	}

	function database($name) {
		$this->tables .= "DROP DATABASE IF EXISTS `$name`;
CREATE DATABASE IF NOT EXISTS `$name` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `$name`;\n\n";
		return $this;
	}
	
	function base($name='name') {
		return $this->id('id')
			->stamp('createdtime', false)
			->stamp('modifiedtime')
			->stamp('tombstonetime')
			->string($name);
	}
	
	function table($name) {
		$this->currenttable = $name;
		$this->tables .= "CREATE TABLE `$name` (\n";
		return $this;
	}
	
	function id($name) {
		$this->tables .= "\t`$name` bigint(20) UNSIGNED NOT NULL,\n";
		$this->keys .= "ALTER TABLE `{$this->currenttable}` ADD PRIMARY KEY (`$name`);\n";
		$this->keys .= "ALTER TABLE `{$this->currenttable}` MODIFY `$name` int(5) NOT NULL AUTO_INCREMENT;\n\n";
		return $this;
	}
	
	function fid($name, $null=false) {
		$this->tables .= "\t`$name` bigint(20) UNSIGNED ".($null?'NULL':'NOT NULL').",\n";
		return $this;
	}

	function integer($name, $unsigned=true) {
		return $this->int($name, $unsigned);
	}
	
	function int($name, $unsigned=true) {
		$this->tables .= "\t`$name` int ".($unsigned?'UNSIGNED':'')." DEFAULT 0,\n";
		return $this;
	}

	function bigint($name, $unsigned=true) {
		$this->tables .= "\t`$name` bigint ".($unsigned?'UNSIGNED':'')." DEFAULT 0,\n";
		return $this;
	}
	
	function stamp($name, $defaultnull = true) {
		$this->tables .= "\t`$name` timestamp NULL DEFAULT ".($defaultnull?'NULL':'CURRENT_TIMESTAMP').",\n";
		return $this;
	}
	
	function string($name, $size=191) {
		$this->tables .= "\t`$name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,\n";
		return $this;
	}
	
	function url($name) {
		return $this->string($name, 191);
	}
	
	function text($name) {
		$this->tables .= "\t`$name` text COLLATE utf8mb4_unicode_ci NOT NULL,\n";
		return $this;
	}
	
	function boolean($name) {
		$this->tables .= "\t`$name` tinyint(3) UNSIGNED NOT NULL DEFAULT '0',\n";
		return $this;
	}
	
	function endtable() {
		$this->tables = substr($this->tables, 0, -2) . "\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";
		return $this;
	}
	
	function go() {
		$this->db->statement($this->tables);
		$this->db->statement($this->keys);		
	}
	
	function insert($name) {
		$args = func_get_args();
		$numargs = func_num_args();
		
		$sql = "INSERT INTO `{$args[0]}` (";

		for ($i = 1; $i < $numargs; $i += 2) {
			$sql .= "`{$args[$i]}`,";
		}
		
		$sql = substr($sql, 0, -1) . ") VALUES (";

		for ($i = 2; $i < $numargs; $i += 2) {
			$sql .= "'{$args[$i]}',";
		}

		
		$sql = substr($sql, 0, -1) . ");\n";
		
		echo $sql;
		
		$this->db->statement($sql);
		return $this->db->insertId();
	}
	
}
