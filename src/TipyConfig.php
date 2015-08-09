<?php

// ==================================================================
// Config class
// ==================================================================
require_once('TipyBinder.php');

class TipyConfig extends TipyBinder {

    public function __construct() {
        parent::__construct();
        defined('INI_FILE') || define('INI_FILE', __DIR__.'/../../app/config.ini');
        $iniData = parse_ini_file(INI_FILE);
        $this->bind($iniData);
    }
}
