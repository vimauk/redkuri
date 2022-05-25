<?php

namespace vima\RedKuri;

class BaseDBObject {
    protected $__db;
    protected $__iterator;
    protected $__table;

    function __construct($id = NULL) {
        if ($this->__db == null) {
            RKError(9999, 'No Database specified for class '.get_class($this));
        }

        if ($this->__table == '') {
            $class = substr(get_class($this), strrpos(get_class($this), '\\') );
            $this->__table = Language::pluralise(strtolower($class));
        }

        if (is_array($id)) {
            /* Array passed so hydrate object with array */
            $this->hydrate($id);
        }

        if (!is_null($id) && !is_array($id)) {
            $this->id = $id;

            $res = $this->__db->sql('SELECT * FROM '.$this->__table.' WHERE id=?'.(property_exists($this, 'tombstonetime')?' AND tombstonetime is null':''), array($this->id));

            foreach ($this->allFieldNames() as $name) {
                $this->$name = $res->f($name);
            }
        }
    }

    function hydrate($array) {
        if (!is_array($array)) return false;
        $fields = get_object_vars($this);
        foreach ($fields as $field => $name) {
                if (isset($array[$field])) {
                    $this->$field = $array[$field];
                }
        }

        return true;
    }

    function refresh() {
        if (!is_null($this->id)) {
            $res = $this->__db->query('SELECT * FROM '.$this->__table.' WHERE id=?'.(property_exists($this, 'tombstonetime')?' AND tombstonetime is null':''), array($this->id));
            if ($res->numberRows() == 0) {
                die($this->__table.' - Id not found ('.$id.')');
            }
            foreach ($this->allFieldNames() as $name) {
                $this->$name = $res->f($name);
            }
        }
    }

    public function __call($name, $arguments) {
        $name = strtolower($name);

        if (substr($name, 0, 3) == 'set') {
            if (array_key_exists(0, $arguments)) {
                $field = substr($name,3);
                $this->$field = $arguments[0];
            }
            return $this;
        }

        if (substr($name, 0, 5) == 'clear') {
            $field = substr($name,5);
            $this->$field = null;
            return $this;
        }

        if (substr($name, 0, 3) == 'get') {
            $field = substr($name, 3);
        } else {
            $field = $name;
        }

		if ($this->fieldExists($field)) {
			return $this->$field;
		}
    }

    function all($select = '*', $where = null, $whereparams = null, $join=null) {
        $sql = 'SELECT '.$select.' FROM '.$this->__table;

        if ($join != null) $sql .= ' '.$join;

        $w = array();

        if (property_exists($this, 'tombstonetime')) $w[] = 'tombstonetime is null';
        if ($where != null) $w[] = $where;

        if (count($w)) {
            $sql .= ' WHERE '.implode($w, ' AND ');
        }

        $this->__iterator = $this->__db->query($sql, $whereparams);
        $this->__iterator->setClassname(get_class($this));
        if ($this->__iterator->numRows() == 0)
            return null;

        return $this->__iterator;
    }

    function next() {
        if ($this->__iterator->nextRow() == false) {
            return null;
        }
        return $this->__iterator;
    }

    function numberRows() {
        if ($this->__iterator != null) {
            return $this->__iterator->numberRows();
        }
        return null;
    }

    function allFieldNames() {
        $names = array();
        $fields = get_object_vars($this);
        foreach ($fields as $field => $name) {
            if (substr($field, 0, 1) != '_') {
                $names[] = $field;
            }
        }
        return $names;
    }

    function allFields() {
        $fields = get_object_vars($this);
        foreach ($fields as $field) {
            if (substr($field, 0, 1) == '_') {
                unset($fields[$field]);
            }
        }
        return $fields;
    }

    function fieldExists($name) {
        return property_exists($this, $name);
    }

    function sqlnow() {
        return date('Y-m-d H:i:s');
    }

    function save() {
        if ($this->id == NULL) {
            if (property_exists($this, 'modifiedtime')) {
                $this->createdtime = $this->modifiedtime = $this->sqlnow();
            }
            foreach ($this->allFieldNames() as $name) {
                if ($name <> 'id') {
                    $values[] = $this->$name;
                    $names[] = $name;
                    $questions[] = '?';
                }
            }
            if ($this->__db == NULL) {
                trigger_error('DB is NULL');
            }
            try {
                $this->__db->statement('INSERT INTO '.$this->__table.' (`'.implode('`,`', $names).'`) VALUES ('.implode(',', $questions).')', $values);
            } catch (PDOException $e) {
                redkuri_error(0,'Error: '.$e->GetMessage());
            }
            $this->id = $this->__db->insertId();
            return true;
        } else {
            if (property_exists($this, 'modifiedtime')) {
                $this->modifiedtime = $this->sqlnow();
            }
            foreach ($this->allFieldNames() as $name) {
                if ($name != 'id') {
                    $values[] = $this->$name;
                    $set[] = $name.'=?';
                }
            }
            $values[] = $this->id;
            $this->__db->statement('UPDATE '.$this->__table.' SET '.implode(',', $set).' WHERE id=?', $values);

            return true;
        }
    }

    function delete() {
        $this->tombstonetime = $this->sqlnow();
        $this->save();
    }

    function undelete() {
        $this->tombstonetime = null;
        $this->save();
    }
}

