<?php
/**
 *
 *  Load tipy classes and start application
 *
 */

ob_start();

require_once('ErrorHandler.php');
require_once('ErrorHandler.php');
require_once('TipyApp.php');
require_once('TipyDAO.php');
require_once('TipyModel.php');
require_once('TipyConfig.php');
require_once('TipyEnv.php');
require_once('TipyCookie.php');
require_once('TipyInput.php');
require_once('TipyOutput.php');
require_once('TipyView.php');
require_once('Inflector.php');
require_once('TipyAutoloader.php');

srand((double)microtime()*1000000);
$app = TipyApp::getInstance();
$app->run();

ob_end_flush();
