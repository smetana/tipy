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
     * Request URI
     * @return string
     */
    public function uri() {
        return $this->get('REQUEST_URI');
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
        return $this->method() == 'GET';
    }

    /**
     * Return true if request is a POST request
     * @return boolean
     */
    public function isPost() {
        return $this->method() == 'POST';
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
        return $this->method() == 'PUT';
    }

    /**
     * Return true if request is a DELETE request
     * @return boolean
     */
    public function isDelete() {
        return $this->method() == 'DELETE';
    }

    /**
     * Return true if request is a OPTIONS request
     * @return boolean
     */
    public function isOptions() {
        return $this->method() == 'OPTIONS';
    }

    /**
     * Return true if request is a HEAD request
     * @return boolean
     */
    public function isHead() {
        return $this->method() == 'HEAD';
    }

    /**
    * Returns the IP address of the client.
    *
    * @param bool $trust_proxy_headers Whether or not to trust the
    * proxy headers HTTP_CLIENT_IP and HTTP_X_FORWARDED_FOR.
    * ONLY use if your server is behind a proxy that sets these values
    * @return string
    */
    public function ip($trust_proxy_headers = false) {
        if (!$trust_proxy_headers) {
            return $_SERVER['REMOTE_ADDR'];
        }
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
}
