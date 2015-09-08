<?php
/**
 * TipyController
 *
 * @package tipy
 */

/**
 * C in MVC. Receive the request, fetch or save data from a models, and use a TipyView to create HTML output
 *
 * <code>
 * class MyController extends TipyController {
 *     public function myAction() {
 *         // do things with $this->in, $this->out, etc...
 *         $this->renderView('my/myAction')
 *     }
 * }
 * </code>
 */
class TipyController {

    /**
     * @var TipyConfig
     */
    public $config;

    /**
     * @var TipyInput
     */
    public $in;

    /**
     * @var TipyOutput
     */
    public $out;

    /**
     * @var TipyEnv
     */
    public $env;

    /**
     * @var TipyView
     */
    public $view;

    /**
     * @var mysqli|null
     */
    public $db;

    /**
     * @var TipySession
     */
    public $session;

    /**
     * @var TipyCookie
     */
    public $cookie;

    /**
     * @var TipyFlash
     */
    protected $flash;

    /**
     * Instantiate controller with application context
     */
    public function __construct() {
        $app = TipyApp::getInstance();
        $this->config   = $app->config;
        $this->request  = $app->request;
        $this->in       = $app->in;
        $this->out      = $app->out;
        $this->env      = $app->env;
        $this->cookie   = $app->cookie;
        $this->view     = $app->view;
        $this->db       = $app->db;
        $this->session  = $app->session;
        $this->flash    = new TipyFlash($this->session);
    }

    /**
     * Shortcut to $this->in->get()
     * @param string $key
     * @param mixed $defaultValue
     * @return mixed
     */
    public function in($key, $defaultValue = null) {
        return $this->in->get($key, $defaultValue);
    }

    /**
     * Shortcut to $this->in->set()
     * @param string $key
     * @param mixed $value
     */
    public function out($key, $value) {
        return $this->out->set($key, $value);
    }

    /**
     * Render template and return result as a string
     * @param string $templateName
     * @return string
     */
    public function renderTemplate($templateName) {
        $this->executeAfter();
        $this->view->bind($this->out->getAll());
        $output = $this->view->processTemplate($templateName);
        return $output;
    }

    /**
     * Render template to output buffer
     * @param string $templateName
     */
    public function renderView($templateName) {
        echo $this->renderTemplate($templateName);
    }

    /**
     * Execute controller action by name with all before/after hooks
     * @param string $action
     */
    public function execute($action) {
        $this->executeBefore();
        $this->$action();
    }

    /**
     * Execute function wrapper. In TEST_MODE it does not exit but
     * throws exception to continue tests
     * @param string $action
     */
    private function safeExit($message) {
        if (defined('TEST_MODE') and TEST_MODE) {
            throw new TipyException($msg);
        } else {
            exit;
        }
    }

    /**
     * Redirect
     * @param string $path
     */
    public function redirect($path) {
        while (@ob_end_clean()) {
        }
        header('HTTP/1.0 302 Moved Temporarily');
        header('Location: '.$path);
        $this->safeExit('Redirected to '.$path);
    }

    /**
     * 404 
     * @todo Implement fancy 404 page
     */
    public function pageNotFound() {
        while (@ob_end_clean()) {
        }
        header('HTTP/1.0 404 Not Found');
        $this->safeExit('Status: 404 Not Found');
    }

    /**
     * Hook to execute before action. Override this in your controller.
     */
    public function executeBefore() {
    }

    /**
     * Hook to executed after action. Override this in your controller.
     */
    public function executeAfter() {
    }
}
