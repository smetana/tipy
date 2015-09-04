<?php
/**
 * TipyCookie
 *
 * @package tipy
 */

/**
 * Access cookies the same way as any other tipy input/output objects
 *
 * Usage:
 * <code>
 * class MyController extends TipyController {
 *     public function index() {
 *         $value = $this->cookie->get('myVar1');
 *         $this->cookie->set('myVar2', 'Hello World!');
 *         // ...
 *     }
 * }
 * </code>
 */
class TipyCookie {

    private $cookies;

    /**
     * Construct TipyCookie instance from $_COOKIE.
     */
    public function __construct() {
        $this->cookies = $_COOKIE;
    }

    /**
     * Get cookie variable by its name
     * @param string $key
     * @param mixed $defaultValue
     * @return mixed
     */
    public function get($key) {
        if (isset($this->cookies[$key])) {
            return $this->cookies[$key];
        } else {
            if (func_num_args() > 1) {
                return func_get_arg(1);
            } else {
                return null;
            }
        }
    }

    /**
     * Set cookie variable. You may pass unix timestamp as optional argument to set cookie expire time.
     * @param string $key
     * @param mixed $value
     * @param integer $expireTime
     * @return mixed
     */
    public function set($key, $value, $expireTime = 0) {
        setcookie($key, $value, $expireTime, '/');
        $this->cookies[$key] = $value;
    }

    /**
     * Get all cookies as array
     * @return array
     */
    public function getAll() {
        return $this->cookies;
    }
}
