<?php

// ==================================================================
// Input
// ==================================================================

class TipyInput extends TipyBinder {

    function __construct() {
        parent::__construct();
        $this->bind($_REQUEST);
    }

}

