<?php
// If tipy run in 'cli' SAPI then we don't need to
// initialize outout buffer and run application.
// Just require classes needed.
define('CLI_MODE', php_sapi_name() == 'cli');

require_once('src/TipyErrorHandler.php');
require_once('src/TipyBinder.php');
require_once('src/TipyConfig.php');
require_once('src/TipyEnv.php');
require_once('src/TipyCookie.php');
require_once('src/TipyInput.php');
require_once('src/TipyOutput.php');
// No server sessions in CLI_MODE
CLI_MODE || require_once('src/TipySession.php');
require_once('src/TipyMailer.php');
require_once('src/TipyDAO.php');
require_once('src/Inflector.php');
require_once('src/TipyModel.php');
require_once('src/TipyView.php');
require_once('src/TipyFlash.php');
require_once('src/TipyController.php');
require_once('src/TipyRouter.php');
require_once('src/TipyApp.php');

class Tipy {
    public static function run() {
        CLI_MODE && die("TipyApp cannot be run in CLI mode\n");
        ob_start();
        $app = TipyApp::getInstance();
        $app->run();
        ob_end_flush();
    }
}
