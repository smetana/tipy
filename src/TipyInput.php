<?php

// ==================================================================
// Input
// ==================================================================

class TipyInput extends TipyIOWrapper {

    public function __construct() {
        parent::__construct();
        $this->bind($_REQUEST);
    }
}
