<?php
//
//  +---------------------------------------------------------------+
//  | Main execution script.
//  +---------------------------------------------------------------+
//  | Performs basic inital application tasks:
//  | - Provides include path and application-scope constants
//  +---------------------------------------------------------------+
//

//
// Set here path to ini file.
//
define('INI_FILE', __DIR__.'/../app/config.ini');

//
// run application
//
require(__DIR__.'/../vendor/tipy/Bootstrap.php');