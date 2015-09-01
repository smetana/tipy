<?php
/**
 * TipyView
 *
 * @package tipy
 */

/**
 * HTML template engine based on PHP output buffer and PHP itself
 *
 * Usage:
 * <code>
 * // app/controllers/HelloWorldController.php
 * class HelloWorldController extends TipyController {
 *     public function article() {
 *         $this->out('title', 'Hello');
 *         $this->out('message', 'World!');
 *         $this->renderView('helloWorld');
 *     }
 * }
 * </code>
 *
 * <code>
 * // app/views/helloWorld.php
 * <!DOCTYPE html>
 * <html>
 * <head>
 *     <title><?= $title ></title>
 * </head>
 * <body>
 *     <p><?= $title.' '.$message ?></p>
 * </body>
 * </html>
 * </code>
 *
 * You can wrap one template in another one
 *
 *
 */
class TipyView {

    /**
     * variables assigned to templates
     * @internal
     */
    private $assigns;

    /**
     * absolute path to template files
     * @internal
     */
    private $templatePath;

    /**
     * These two stacks are used when processed template (or its part)
     * is nested into other template.
     * @see TipyView::applyTemplateStart()
     * @see TipyView::applyTemplateEnd()
     * @internal
     */
    private $templateStack;
    private $contentStack;


    public function __construct() {
        $this->assigns          = new TipyIOWrapper();
        $this->contentStack     = [];
        $this->templateStack    = [];
    }

    /**
     * Assign variables map to template.
     * This will replace all output data.
     */
    public function bind(array $map) {
        $this->assigns->bind($map);
    }

    /**
     * Get value of the variable assigned to template by variable name.
     * If $key does not exists may return $defaultValue.
     *
     * @param string $key
     * @param mixed $defaultValue
     * @return mixed
     */
    public function get($key) {
        if (func_num_args() > 1) {
            return $this->assigns->get($key, $func_get_arg(1));
        } else {
            return $this->assigns->get($key);
        }
    }

    /**
     * Assign variable to template
     *
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value) {
        $this->assigns->set($key, $value);
    }

    /**
     * Get all assigned variables
     *
     * @return array
     */
    public function getAll() {
        return $this->assigns->getAll();
    }

    /**
     * Set path to templates
     *
     * @param string $path
     */
    public function setTemplatePath($path) {
        $this->templatePath = $path;
    }

    /**
     * Compile template and return result as a string
     *
     * @param string $templateName
     * @return string
     */
    public function processTemplate($templateName) {
        $templateFile = $this->expandTemplatePath($templateName);
        $vars = $this->assigns->getAll();
        extract($vars);
        $output = "";
        ob_start();
        include($templateFile);
        $output = ob_get_clean();
        return $output;
    }

    /**
     * Get full path to template file
     *
     * @param string $templateName
     * @return string
     */
    public function expandTemplatePath($templateName) {
        return $this->templatePath . "/" . $templateName . ".php";
    }

    // --------------------------------------------------------------
    // include template
    // --------------------------------------------------------------
    private function includeTemplate($templateName) {
        $templateFile = $this->expandTemplatePath($templateName);
        $vars = $this->assigns->getAll();
        extract($vars);
        include($templateFile);
    }

    // --------------------------------------------------------------
    // Apply template over processed one
    // Entry point
    // NOTE: this method is to be called as callback from template
    // --------------------------------------------------------------
    private function applyTemplateStart($templateName) {
        // Put template name into stack. We will use it
        $this->templateStack[] = $templateName;
        // And start processing
        ob_start();
    }

    // --------------------------------------------------------------
    // Exit point for the applyTemplate
    // NOTE: This method is to be called as callback
    // --------------------------------------------------------------
    private function applyTemplateEnd() {
        // Get what we have processed and put it into stack
        $this->contentStack[] = ob_get_contents();
        ob_end_clean();
        // Get template name from stack and process it
        $output = $this->processTemplate(array_pop($this->templateStack));
        print $output;
        array_pop($this->contentStack);
    }

    // --------------------------------------------------------------
    // This method is to be used inside template processed over
    // another template
    // --------------------------------------------------------------
    private function childContent() {
        $stacksize = sizeof($this->contentStack);
        // if we have something in stack then return last value
        if ($stacksize > 0) {
            return $this->contentStack[$stacksize-1];
        } else {
            return '';
        }
    }

}
