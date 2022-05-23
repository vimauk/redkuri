<?php

namespace vima\RedKuri;

class Database {
	protected $__db;
	protected $__lastquery;

	function __construct($hostname, $database, $username, $password, $type='mysql') {
		$this->__lastquery = null;
		if (!is_object($this->__db)) {
			try {
				switch ($type) {
					case 'oracle':
						debug('Oracle Not Implemented');
						break;
					case 'mysql':
						$this->__db = new \PDO("mysql:host=$hostname;dbname=$database", $username, $password);
						$this->__db->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
						$this->__db->setAttribute( \PDO::ATTR_EMULATE_PREPARES, false ); // To prevent Integer casting
						break;
					default:
						$this->__db = new PDO("sqlsrv:server=$hostname;database=$database", $username, $password);
//					$this->__db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
				}
			} catch (\PDOException $e) {
				RKError(0,'Error: '.$e->GetMessage());
			}
		}
		return $this->__db;
	}

	function lastQuery() {
		return $this->__lastquery;
	}

	function insertId() {
		return $this->__db->lastInsertId();
	}

	function sql($query, $params = null) {
		$this->__lastquery = $query;
		if ($params == null) {
			try {
				$result = $this->__db->query($query);
				return new Resultset($result);
			} catch (PDOException $e) {
				RKError(0,'Error: '.$e->GetMessage());
			}
		} else {
			try {
				$statement = $this->__db->prepare($query);
				$statement->execute($params);
				return new Resultset($statement);
			} catch (PDOException $e) {
				echo $e->GetMessage();
				RKError(0,'Error: '.$e->GetMessage());
			}
		}
	}

	function query($query, $params = null) {
		return $this->sql($query, $params);
	}

	function getArray($query, $params = null) {
//		$result = $this->sql($query, $params);
		$this->__lastquery = $query;
		if ($params == null) {
			try {
				$result = $this->__db->query($query);
				return $result->fetchAll();
			} catch (PDOException $e) {
				RKError(0,'Error: '.$e->GetMessage());
			}
		} else {
			try {
				$statement = $this->__db->prepare($query);
				$statement->execute($params);
				return $statement->fetchAll();
			} catch (PDOException $e) {
				echo $e->GetMessage();
				RKError(0,'Error: '.$e->GetMessage());
			}
		}
	}

	function statement($query, $params = null) {
		$this->__lastquery = $query;
		if ($params != null) {
			try {
				$statement = $this->__db->prepare($query);
				$statement->execute($params);
			} catch (PDOException $e) {
				RKError(0,'Error: '.$e->GetMessage());
			}
		} else {
			try {
				$result = $this->__db->exec($query);
			} catch (PDOException $e) {
				RKError(0,'Error: '.$e->GetMessage());
			}
		}
	}

	function startTrans() {
		$this->__db->beginTransaction();
	}

	function endTrans() {
		return $this->__db->commit();
	}

	function rollback() {
		return $this->__db->rollBack();
	}

	function link() {
		return $this->__db;
	}
}

