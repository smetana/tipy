<?php

// ==================================================================
// Simple Router
// For now mode_rewrite is used
// ==================================================================

class TipyRouter {

    protected $routes;

    public function __construct() {
        $this->routes = array();
        $this->defineRoutes();
    }


    public function defineRoutes() {
       // Override me
    }

    public function getRoute($route) {

    }
}
