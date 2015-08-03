<?php

// ==================================================================
// Cookie class
// ==================================================================

class TipyCookie {

    private $cookies;

    // --------------------------------------------------------------
    // Constructor
    // --------------------------------------------------------------
    function __construct() {
        $this->cookies = $_COOKIE;
    }

    // --------------------------------------------------------------
    // Get session variable, default value or null
    // --------------------------------------------------------------
    function get($varname) {
        if (isset($this->cookies[$varname])) {
            return $this->cookies[$varname];
        } else {
            if (func_num_args() > 1) {
                return func_get_arg(1);
            } else {
                return NULL;
            }
        }
    }

    // --------------------------------------------------------------
    // Set cookie variable
    // --------------------------------------------------------------
    function set($varname, $value, $expire = 0) {
        setcookie($varname, $value, $expire, '/');
        $this->cookies[$varname] = $value;
    }

    // --------------------------------------------------------------
    // Get all cookies
    // --------------------------------------------------------------
    function getAll() {
        return $this->cookies;
    }
}

