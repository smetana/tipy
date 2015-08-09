<?php

// ==================================================================
// Input
// ==================================================================

class TipyInput extends TipyBinder {

    public function __construct() {
        parent::__construct();
        $this->bind($_REQUEST);
    }
}
