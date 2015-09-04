<?php
/**
 * TipyController
 *
 * @package tipy
 */

/**
 * Base controller class
 *
 * Usage:
 * <code>
 * class MyController extends TipyController {
 *     public function myAction() {
 *         // do things with $this->in, $this->out, etc...
 *         $this->renderView('my/myAction')
 *     }
 * }
 * </code>
 *
 * <a name="routing"></a>
 * <h2>Routing</h2>
 *
 * Tipy uses <b>.htaccess</b> for routing.
 * .htaccess RewriteRules rewrite all request urls to something like:
 * <code>
 * dispatcher.php?controller=my&method=my_action&id=... # => MyController::myAction()
 * </code>
 *
 * All query string parameters will be preserved on url rewite.
 *
 * <b>controller</b> parameter represents controller class with:
 *
 * - snake_case transformed to CamelCase (first letter in uppper case)
 * - word "Controller" is added to the end
 *
 * <b>action</b> parameter represents controller's method with:
 *
 * - snake_case is transformed to camelCase (first letter in lower case)
 *
 * <h2>Predefined Routes</h2>
 *
 * Tipy also comes with a set of predefined rules so you don't need to
 * rewrite urls to dispatcher.php. It is enough to rewrite urls to one of the
 * following form:
 * <code>
 * /:controller              # /source_code               => SourceCodeController::index();
 * /:controller/:action      # /source_code/open_source   => SourceCodeController::openSource();
 * /:controller/:action/:id  # /source_code/line_number/3 => SourceCodeController::lineNumber($id = 3);
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
