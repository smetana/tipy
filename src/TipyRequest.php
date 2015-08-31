<?php

class TipyRequest extends TipyIOWrapper {

    public function __construct() {
        parent::__construct();
        $this->bind($_SERVER);
    }

    public function method() {
        return $this->get('REQUEST_METHOD');
    }

    public function isGet() {
        return $this->method == 'GET';
    }

    public function isPost() {
        return $this->method == 'POST';
    }

    public function isXhr() {
        return $this->get('HTTP_X_REQUESTED_WITH') == 'XMLHttpRequest';
    }

}
