<?php

// ==================================================================
// Session class
// ==================================================================

class TipySession {

    public function __construct() {
        $this->start();
    }

    // --------------------------------------------------------------
    // Start session
    // --------------------------------------------------------------
    public function start() {
        if (!session_id()) {
            session_set_cookie_params($this->get('sessionExpires', 0), '/');
            session_start();
        }
    }

    public function setLifetime($time = 0) {
        $this->set('sessionExpires', $time);
        session_set_cookie_params($this->get('sessionExpires', 0), '/');
        session_regenerate_id();
    }

    // --------------------------------------------------------------
    // Close session
    // --------------------------------------------------------------
    public function close() {
        $_SESSION = array();
        // If it's desired to kill the session, also delete the session cookie.
        // Note: This will destroy the session, and not just the session data!
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', 1, '/');
        }
        // Finally, destroy the session.
        session_destroy();
    }

    // --------------------------------------------------------------
    // Set session data
    // --------------------------------------------------------------
    public function bind($data) {
        $_SESSION = $data;
    }

    // --------------------------------------------------------------
    // Get session variable, default value or null
    // --------------------------------------------------------------
    public function get($varname) {
        if (isset($_SESSION[$varname])) {
            return $_SESSION[$varname];
        } else {
            if (func_num_args() > 1) {
                return func_get_arg(1);
            } else {
                return null;
            }
        }
    }

    // --------------------------------------------------------------
    // Set session variable
    // --------------------------------------------------------------
    public function set($varname, $value) {
        $_SESSION[$varname] = $value;
    }

    // --------------------------------------------------------------
    // Get all session vars
    // --------------------------------------------------------------
    public function getAll() {
        return $_SESSION;
    }
}
