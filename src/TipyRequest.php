<?php
/**
 * TipyRequest
 *
 * @package tipy
 */

/**
 * Access request and its header
 *
 * By default it is a TipyIOWrapper around $_SERVER superglobal
 * @see TipyIOWrapper
 */
class TipyRequest extends TipyIOWrapper {

    /**
     * @internal
     */
    public function __construct() {
        parent::__construct();
        // little hack to autocreate $_SERVER if
        // auto_globals_jit is on
        $foo = $_SERVER['PHP_SELF'];
        $this->bind($_SERVER);
    }

    /**
     * Return request method: GET,POST,DELETE,etc...
     * @return string
     */
    public function method() {
        return $this->get('REQUEST_METHOD');
    }

    /**
     * Return true if request is a GET request
     * @return boolean
     */
    public function isGet() {
        return $this->method == 'GET';
    }

    /**
     * Return true if request is a POST request
     * @return boolean
     */
    public function isPost() {
        return $this->method == 'POST';
    }

    /**
     * Returns true if request is AJAX request
     * @return boolean
     */
    public function isXhr() {
        return $this->get('HTTP_X_REQUESTED_WITH') == 'XMLHttpRequest';
    }

}
