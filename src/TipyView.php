<?php

// ==================================================================
// View class
// Template engine based on php itself and it's output bufer
// ==================================================================

class TipyView {

    private $assigns; // Stores variable assigned to template
    private $templatePath;

    // This two stacks are used when processed template (or the part)
    // is nested into other template.
    // See applyTemplateStart and applyTemplateEnd below for details
    private $templateStack;
    private $contentStack;

    // --------------------------------------------------------------
    // Contrustor
    // --------------------------------------------------------------
    public function __construct() {
        $this->assigns          = new TipyIOWrapper();
        $this->contentStack     = [];
        $this->templateStack    = [];
    }

    // --------------------------------------------------------------
    // Bind array to assigns
    // --------------------------------------------------------------
    public function bind($data) {
        $this->assigns->bind($data);
    }

    // --------------------------------------------------------------
    // Get assigned variable
    // --------------------------------------------------------------
    public function get($varname) {
        if (func_num_args() > 1) {
            return $this->assigns->get($varname, $func_get_arg(1));
        } else {
            return $this->assigns->get($varname);
        }
    }

    // --------------------------------------------------------------
    // Assign variable to template
    // --------------------------------------------------------------
    public function set($varname, $value) {
        $this->assigns->set($varname, $value);
    }

    // --------------------------------------------------------------
    // Get all assigned variables
    // --------------------------------------------------------------
    public function getAll() {
        return $this->assigns->getAll();
    }

    // --------------------------------------------------------------
    // Set path to templates
    // --------------------------------------------------------------
    public function setTemplatePath($path) {
        $this->templatePath = $path;
    }

    // --------------------------------------------------------------
    // Process template
    // --------------------------------------------------------------
    public function processTemplate($templateName) {
        $templateName = $this->templateName($templateName);
        $vars = $this->assigns->getAll();
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

    // --------------------------------------------------------------
    // include template
    // --------------------------------------------------------------
    private function includeTemplate($templateName) {
        $templateName = $this->templateName($templateName);
        $vars = $this->assigns->getAll();
        extract($vars);
        include($templateName);
    }
}
