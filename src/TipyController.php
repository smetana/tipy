<?php

// ==================================================================
// Base controller class
// ==================================================================

class TipyController {

    public $config;            // Config
    public $in;                // Input wrapper
    public $out;               // Output data holder
    public $env;               // Environment wrapper
    public $view;              // Template Renderer
    public $db;                // DB Handler
    public $session;           // Session
    public $cookie;            // Cookies placeholder
    protected $flash;

    // Application Constructor
    public function __construct() {
        $app = Tipy::getInstance();
        $this->config     = $app->config;
        $this->in         = $app->in;
        $this->out        = $app->out;
        $this->env        = $app->env;
        $this->cookie     = $app->cookie;
        $this->view       = $app->view;
        $this->db         = $app->db;
        $this->session    = $app->session;
        $this->flash      = new TipyFlash($this->session);
    }

    public function in($name, $defaultValue = null) {
        return $this->in->get($name, $defaultValue);
    }

    public function out($name, $value) {
        return $this->out->set($name, $value);
    }

    // --------------------------------------------------------------
    // Debug fuction
    // --------------------------------------------------------------
    public function dump() {
        print_r($this);
    }

    // --------------------------------------------------------------
    // rturn View as string
    // --------------------------------------------------------------
    public function renderTemplate($templateName) {
        $this->executeAfter();
        $this->view->bind($this->out->getAll());
        $output = $this->view->processTemplate($templateName);
        return $output;
    }

    // --------------------------------------------------------------
    // render View (show output)
    // --------------------------------------------------------------
    public function renderView($templateName) {
        echo $this->renderTemplate($templateName);
    }

    // --------------------------------------------------------------
    // Execute default methods before method
    // execute methoid itseld and then do some postactions
    // --------------------------------------------------------------
    public function execute($method) {
        $this->executeBefore();
        $this->$method();
    }

    // --------------------------------------------------------------
    // exit function wrapper for tests compability
    // --------------------------------------------------------------
    public function safeExit($message) {
        if(defined('TEST_MODE') and TEST_MODE) {
            throw new TipyException($msg);
        } else {
            exit;
        }
    }

    // --------------------------------------------------------------
    // Redirect
    // --------------------------------------------------------------
    public function redirect($path) {
        while (@ob_end_clean());
        header('HTTP/1.0 302 Moved Temporarily');
        header('Location: '.$path);
        $this->safeExit('Redirected to '.$path);
    }

    // --------------------------------------------------------------
    // 404
    // --------------------------------------------------------------
    public function pageNotFound() {
        while (@ob_end_clean());
        header('HTTP/1.0 404 Not Found');
        $this->safeExit('Status: 404 Not Found');
    }

    // --------------------------------------------------------------
    // Default actions that are to be executed by default
    // before controller method
    // --------------------------------------------------------------
    public function executeBefore() {
        // override this
    }

    // --------------------------------------------------------------
    // Default actions that are to be executed by default
    // after controller method
    // --------------------------------------------------------------
    public function executeAfter() {
        // override this
    }

}
