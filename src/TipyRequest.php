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
 *
 * <code>
 * class MyController extends TipyController {
 *     public function index() {
 *         $requestIsPost = $this->request->isPost();
 *         $userAgent = $this->request->get('HTTP_USER_AGENT');
 *         // ...
 *     }
 * }
 * </code>
 *
 * @see TipyIOWrapper
 */
class TipyRequest extends TipyIOWrapper {

    /**
     * Construct TipyRequest instance from $_SERVER
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

    /**
     * Return true if request is a PUT request
     * @return boolean
     */
    public function isPut() {
        return $this->method == 'PUT';
    }

    /**
     * Return true if request is a DELETE request
     * @return boolean
     */
    public function isDelete() {
        return $this->method == 'DELETE';
    }

    /**
     * Return true if request is a OPTIONS request
     * @return boolean
     */
    public function isOptions() {
        return $this->method == 'OPTIONS';
    }

    /**
     * Return true if request is a HEAD request
     * @return boolean
     */
    public function isHead() {
        return $this->method == 'HEAD';
    }
}
