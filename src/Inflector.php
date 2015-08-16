<?php

require_once(__DIR__.'/../vendor/Inflect/Inflect.php');

class Inflector extends Inflect {
    public static function underscore($str) {
        $str = preg_replace("/([a-z0-9])([A-Z])/", "$1_$2", $str);
        return strtolower($str);
    }

    public static function camelCase($str) {
        $str = str_replace("_", " ", $str);
        $str = ucwords($str);
        $str = str_replace(" ", "", $str);
        return lcfirst($str);
    }

    public static function classify($str) {
        $str = self::camelCase($str);
        $str = Inflect::singularize($str);
        return ucfirst($str);
    }
}
