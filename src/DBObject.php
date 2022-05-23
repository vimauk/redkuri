<?php

namespace vima\RedKuri;

class DBObject extends BaseDBObject {
    protected $id;
    protected $createdtime;
    protected $modifiedtime;
    protected $tombstonetime;

    function __construct($id = NULL) {
        parent::__construct($id);
        if (is_null($id)) {
            $this->tombstonetime = null;
        }
    }
}