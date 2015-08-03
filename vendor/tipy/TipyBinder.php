<?php

// ==================================================================
// Tipy binder
// Class to store data structures
// ==================================================================

class TipyBinder {

    private $binderData;

    function __construct() {
        $this->binderData = array();
    }

    // --------------------------------------------------------------
    // Set Binder data
    // --------------------------------------------------------------
    function bind(Array $data) {
        $this->binderData = $data;
    }

    // --------------------------------------------------------------
    // Get binder variable, default value or null
    // --------------------------------------------------------------
    function get($varname, $defaultValue = null) {
        return array_key_exists($varname, $this->binderData) ? $this->binderData[$varname] : $defaultValue;
    }

    // --------------------------------------------------------------
    // Set binder variable
    // --------------------------------------------------------------
    function set($varname, $value) {
        $this->binderData[$varname] = $value;
    }

    // --------------------------------------------------------------
    // Get all binder structure
    // --------------------------------------------------------------
    function getAll() {
        return $this->binderData;
    }

    // --------------------------------------------------------------
    // Clear binder data
    // --------------------------------------------------------------
    function clear() {
        $this->binderData = array();
    }

}
