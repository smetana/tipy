<?php

class TipyFlash {

    private $session;
    private $currentMessage;

    public function __construct($session) {
        $this->session = $session;
        $this->currentMessage = $this->session->get('flashMessage');
        $this->session->set('flashMessage', null);
    }

    function set($value) {
        $this->session->set('flashMessage', $value);
    }

    function get() {
        return $this->currentMessage;
    }

}
