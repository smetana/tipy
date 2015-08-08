<?php

// ==================================================================
// View class
// Template engine based on php itself and it's output bufer
// ==================================================================

class TipyView {

    private $binder;              // Internal param binder
    private $templatePath;        // Path to templates

    // This two stacks are used when processed template (or the part)
    // is nested into other template.
    // See applyTemplateStart and applyTemplateEnd below for details
    private $templateStack;
    private $contentStack;

    // --------------------------------------------------------------
    // Contrustor
    // --------------------------------------------------------------
    function __construct() {
        $this->binder           = new TipyBinder();
        $this->contentStack     = array();
        $this->templateStack    = array();
    }

    // --------------------------------------------------------------
    // Bind data to internal param binder
    // --------------------------------------------------------------
    function bind($data) {
        $this->binder->bind($data);
    }

    // --------------------------------------------------------------
    // Get variable from binder
    // --------------------------------------------------------------
    function get($varname) {
        if (func_num_args() > 1) {
            return $this->binder->get($varname, $func_get_arg(1));
        } else {
            return $this->binder->get($varname);
        }
    }

    // --------------------------------------------------------------
    // Set binder variable
    // --------------------------------------------------------------
    function set($varname, $value) {
        $this->binder->set($varname, $value);
    }

    // --------------------------------------------------------------
    // Get all binder variables
    // --------------------------------------------------------------
    function getAll() {
        return $this->binder->getAll();
    }

    // --------------------------------------------------------------
    // Set path to templates
    // --------------------------------------------------------------
    function setTemplatePath($path) {
        $this->templatePath = $path;
    }

    // --------------------------------------------------------------
    // Process template with internal binder vars 
    // --------------------------------------------------------------
    function processTemplate($templateName) {
        $templateName = $this->templateName($templateName);
        $vars = $this->binder->getAll();
        extract($vars);
        $output = "";
        ob_start();
        include($templateName);
        $output = ob_get_clean();
        return $output;
    }

    // --------------------------------------------------------------
    // Expand template name with path
    // --------------------------------------------------------------
    public function templateName($name) {
        return $this->templatePath . "/" . $name . ".php";
    }

    // --------------------------------------------------------------
    // Apply template over processed one
    // Entry point
    // NOTE: this method is to be called as callback from template
    // --------------------------------------------------------------
    private function applyTemplateStart($templateName) {
        // Put template name into stack. We will use it
        array_push($this->templateStack, $templateName);
        // And start processing
        ob_start();
    }

    // --------------------------------------------------------------
    // Exit point for the applyTemplate
    // NOTE: This method is to be called as callback
    // --------------------------------------------------------------
    private function applyTemplateEnd() {
        // Get what we have processed and put it into stack
        array_push($this->contentStack, ob_get_contents());
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

    // --------------------------------------------------------------
    // include template 
    // --------------------------------------------------------------
    private function includeTemplate($templateName) {
        $templateName = $this->templateName($templateName);
        $vars = $this->binder->getAll();
        extract($vars);
        include($templateName);
    }

}
