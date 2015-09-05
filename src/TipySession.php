<?php
/**
 * TipySession
 *
 * @package tipy
 */

/**
 * Access session data the same way as any other tipy input/output objects
 *
 * <code>
 * class MyController extends TipyController {
 *     public function index() {
 *         $userId = $this->session->get('userId');
 *         $this->session->set('my_variable', 'Hello World!');
 *         // ...
 *     }
 * }
 * </code>
 */
class TipySession {

    /**
     * Construct TipySession instance from $_SESSION.
     * If session is not yet started it will be started immediately.
     * If session is already started if will be resumed.
     */
    public function __construct() {
        $this->start();
    }

    /**
     * Start new or resume existing session
     */
    public function start() {
        if (!session_id()) {
            session_set_cookie_params($this->get('sessionExpires', 0), '/');
            session_start();
        }
    }

    /**
     * Set session lifetime in seconds
     * @param integer $time
     */
    public function setLifetime($time = 0) {
        $this->set('sessionExpires', $time);
        session_set_cookie_params($this->get('sessionExpires', 0), '/');
        session_regenerate_id();
    }

    /**
     * Destroy the session and delete all session data
     */
    public function close() {
        $_SESSION = [];
        // If it's desired to kill the session, also delete the session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', 1, '/');
        }
        session_destroy();
    }

    /**
     * The way to set many variables at once
     *
     * **NOTE:** This will overwrite existing session data
     * @param array $map
     */
    public function bind($map) {
        $_SESSION = $map;
    }

    /**
     * Get session variable by its name
     * @param string $key
     * @param mixed $defaultValue
     * @return mixed
     */
    public function get($key) {
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        } else {
            if (func_num_args() > 1) {
                return func_get_arg(1);
            } else {
                return null;
            }
        }
    }

    /**
     * Set session variable
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    /**
     * Get all data stored in session
     * @return array
     */
    public function getAll() {
        return $_SESSION;
    }
}
