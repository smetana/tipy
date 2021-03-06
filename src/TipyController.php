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
 * // app/controllers/BlogController.php
 * class MyController extends TipyController {
 *     public function myAction() {
 *         // your application logic
 *     }
 * }
 * </code>
 *
 * Application input is available in *$this->in*, application output goes to *$this->out*
 *
 * ## Default template rendering
 *
 * At the end of the action HTML template will be rendered automatically.
 * Automatic template name is contructed from controller name (without "Controller")
 * and action name
 *
 * <code>
 * Action                    Template Path
 * ------------------------------------------
 * BlogController::index()   /app/views/Blog/index.php
 * BlogController::post()    /app/views/Blog/post.php
 * </code>
 *
 * ## Custom template rendering
 *
 * You can explicitely render template with custom name
 *
 * <code>
 * // app/controllers/BlogController.php
 * class BlogController extends TipyController {
 *     public function article() {
 *         $this->out('title', 'Hello');
 *         $this->out('message', 'World!');
 *         $this->renderView('path/to/custom/template');
 *     }
 * }
 * </code>
 *
 * ## Disable TipyView rendering
 *
 * If you use custom template engine or your action outputs formats different
 * from text or HTML you may want to disable default tipy rendering
 *
 * <code>
 * $this->skipRender = true;
 * </code>
 *
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
     * @var string
     */
    private $templateName;

    /**
     * Set this to *true* to turn off TipyView template rendering
     * @var boolean
     */
    public $skipRender = false;

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
        $this->view->bind($this->out->getAll());
        return $this->view->processTemplate($templateName);
    }

    /**
     * Set custom template name for rendering.
     * This method force controller to render template
     * even if {@link $skipRender} is set to true
     * @param string $templateName
     */
    public function renderView($templateName) {
        $this->skipRender = false;
        $this->templateName = $templateName;
    }

    /**
     * Execute controller action by name with all before/after hooks
     * @param string $action
     */
    public function execute($action) {
        $this->executeBefore();
        $this->$action();
        $this->executeAfter();
        if (!$this->skipRender) {
            if (!$this->templateName) {
                $this->template = $this->actionToTemplaName($action);
            }
            // Render template to output buffer
            echo $this->renderTemplate($this->templateName);
        }
    }

    /**
     * Path to template
     * @param string $action
     * @return string
     */
    public function actionToTemplaName($action) {
        $className = get_class($this);
        $className = str_replace('Controller', '', $className);
        $this->templateName = $className.'/'.$action;
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
