<?php

/**
 * TipyIOWrapper
 *
 * Class to wrap input/output superglobals for sanitation/validation.
 */

class TipyIOWrapper {

    private $ioArray;

    public function __construct() {
        $this->ioArray = [];
    }

    public function bind(Array $ioArray) {
        $this->ioArray = $ioArray;
    }

    public function get($varname, $defaultValue = null) {
        return array_key_exists($varname, $this->ioArray) ? $this->ioArray[$varname] : $defaultValue;
    }

    public function set($varname, $value) {
        $this->ioArray[$varname] = $value;
    }

    public function getAll() {
        return $this->ioArray;
    }

    public function clear() {
        $this->ioArray = [];
    }
}
