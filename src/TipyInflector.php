<?php

require_once(__DIR__.'/../vendor/Inflect/Inflect.php');

class TipyInflector extends Inflect {
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

    public static function tableize($str) {
        $str = self::pluralize($str);
        return self::underscore($str);
    }

    public static function controllerize($str) {
        $str = strtolower($str);
        $str = str_replace("_", " ", $str);
        $str = ucwords($str);
        $str = str_replace(" ", "", $str);
        return $str;
    }


}
