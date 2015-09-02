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
 *
 * <b>NOTE: $this</b> is available inside template and gives access to
 * TipyView object which renders current template.
 *
 * Example:
 * <code>
 * <ul>
 *     <li>
 *         <? $this->includeTemplate('item') ?>
 *     </li>
 * </ul>
 * </code>
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

    /**
     * Include template (child) to currently rendering template (parent).
     * All parent template variables will be available to its children as
     * they exist in one context.
     *
     * Should be called from template via <b>$this</b>.
     *
     * Example:
     * <code>
     * <ul>
     *     <li>
     *         <? $this->includeTemplate('item') ?>
     *     </li>
     * </ul>
     * </code>
     * @param string $templateName
     */
    protected function includeTemplate($templateName) {
        $templateFile = $this->expandTemplatePath($templateName);
        $vars = $this->assigns->getAll();
        extract($vars);
        include($templateFile);
    }

    /**
     * Wrap another template (layout) around currently rendering template or even its part (child).
     *
     * Layout template should have <i>$this->childContent()</i> call to specify where child
     * template will be inserted.
     *
     * All defined variables will be available in both templates as they exist in one context.
     *
     * Should be called from template via <b>$this</b>
     *
     * Example:
     * <code>
     * // app/views/child.php
     * <p>
     * <? $this->applyTemplateStart('layout') ?>
     *     TipyView is cool!
     * <? $this->applyTemplateEnd('layout') ?>
     * </p>
     * </code>
     * <code>
     * // app/views/layout.php
     * <strong>
     *     <? $this->childContent() ?>
     * </strong>
     * </code>
     * Will be rendered as
     * <code>
     * <p>
     * <strong>
     *     TipyView is cool!
     * </strong>
     * </p>
     * </code>
     *
     * @param string $templateName
     */
    protected function applyTemplateStart($templateName) {
        // Put template name into stack. We will use it
        $this->templateStack[] = $templateName;
        // And start processing
        ob_start();
    }

    /**
     * Indicates the end of layout
     * @see applyTemplateStart()
     */
    protected function applyTemplateEnd() {
        // Get what we have processed and put it into stack
        $this->contentStack[] = ob_get_contents();
        ob_end_clean();
        // Get template name from stack and process it
        $output = $this->processTemplate(array_pop($this->templateStack));
        print $output;
        array_pop($this->contentStack);
    }

    /**
     * Insert caller template's content into layout template
     * @see applyTemplateStart()
     */
    protected function childContent() {
        $stacksize = sizeof($this->contentStack);
        // if we have something in stack then return last value
        if ($stacksize > 0) {
            return $this->contentStack[$stacksize-1];
        } else {
            return '';
        }
    }

}
