<?php

class TipyRouter {

    public static function match($request, &$controllerName, &$methodName, &$id) {
        $uri = $request->get('REQUEST_URI');
        if (preg_match('/^\/(\w+)($|\?|#)/', $uri, $matches)) {
            $controllerName = TipyInflector::classify($matches[1]).'Controller';
            $methodName = 'index';
            $id = null;
            return true;
        } elseif (preg_match('/^\/(\w+)\/(\w+)($|\?|#)/', $uri, $matches)) {
            $controllerName = TipyInflector::classify($matches[1]).'Controller';
            $methodName = $matches[2];
            $id = null;
            return true;
        } elseif (preg_match('/^\/(\w+)\/(\w+)\/(\d+)($|\?|#)/', $uri, $matches)) {
            $controllerName = TipyInflector::classify($matches[1]).'Controller';
            $methodName = $matches[2];
            $id = intval($matches[3]);
            return true;
        } else {
            return false;
        }
    }
}
