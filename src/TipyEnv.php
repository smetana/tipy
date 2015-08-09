<?php

// ==================================================================
// Environment wrapper
// ==================================================================

class TipyEnv extends TipyBinder {

    public function __construct() {
        parent::__construct();
        // little hack to autocreate $_SERVER if
        // auto_globals_jit is on
        $doesntmatter = $_SERVER['PHP_SELF'];
        // So be it! For now....
        $this->bind($_SERVER);
    }
}
