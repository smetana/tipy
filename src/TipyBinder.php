<?php

// ==================================================================
// Tipy binder
// Class to store data structures
// ==================================================================

class TipyBinder {

    private $binderData;

    public function __construct() {
        $this->binderData = array();
    }

    // --------------------------------------------------------------
    // Set Binder data
    // --------------------------------------------------------------
    public function bind(Array $data) {
        $this->binderData = $data;
    }

    // --------------------------------------------------------------
    // Get binder variable, default value or null
    // --------------------------------------------------------------
    public function get($varname, $defaultValue = null) {
        return array_key_exists($varname, $this->binderData) ? $this->binderData[$varname] : $defaultValue;
    }

    // --------------------------------------------------------------
    // Set binder variable
    // --------------------------------------------------------------
    public function set($varname, $value) {
        $this->binderData[$varname] = $value;
    }

    // --------------------------------------------------------------
    // Get all binder structure
    // --------------------------------------------------------------
    public function getAll() {
        return $this->binderData;
    }

    // --------------------------------------------------------------
    // Clear binder data
    // --------------------------------------------------------------
    public function clear() {
        $this->binderData = array();
    }
}
