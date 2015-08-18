<?php

class TipyRouter {

    public static function match($request, &$controllerName, &$actionName, &$id) {
        $uri = $request->get('REQUEST_URI');
        if (preg_match('/^\/(\w+)($|\?|#)/', $uri, $matches)) {
            $controllerName = $matches[1];
            $actionName = 'index';
            $id = null;
            return true;
        } elseif (preg_match('/^\/(\w+)\/(\w+)($|\?|#)/', $uri, $matches)) {
            $controllerName = $matches[1];
            $actionName = $matches[2];
            $id = null;
            return true;
        } elseif (preg_match('/^\/(\w+)\/(\w+)\/(\d+)($|\?|#)/', $uri, $matches)) {
            $controllerName = $matches[1];
            $actionName = $matches[2];
            $id = intval($matches[3]);
            return true;
        } else {
            return false;
        }
    }
}
