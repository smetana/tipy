<?php
/**
 * TipyInflector
 *
 * Based on the Sho Kuwamoto's library
 * http://kuwamoto.org/2007/12/17/improved-pluralizing-in-php-actionscript-and-ror/
 * @package tipy
 */
require_once(__DIR__.'/../vendor/Inflect/Inflect.php');

/**
 * Transforms words from singular to plural, class names to table names, camelCase to snake_case, etc...
 */
class TipyInflector extends Inflect {

    /**
     * Transform string in camelCase to snake_case
     * @param string $str
     * @return string
     */
    public static function underscore($str) {
        $str = preg_replace("/([a-z0-9])([A-Z])/", "$1_$2", $str);
        return strtolower($str);
    }

    /**
     * Transform string in snake_case to camelCase
     * @param string $str
     * @return string
     */
    public static function camelCase($str) {
        $str = str_replace("_", " ", $str);
        $str = ucwords($str);
        $str = str_replace(" ", "", $str);
        return lcfirst($str);
    }

    /**
     * Create a model class name from a plural table name.
     *
     * <code>
     * TipyInflector::classify('blog_posts') // => BlogPost
     * </code>
     * @param string $str
     * @return string
     */
    public static function classify($str) {
        $str = self::camelCase($str);
        $str = Inflect::singularize($str);
        return ucfirst($str);
    }

    /**
     * Create plural table name from a model name.
     *
     * <code>
     * TipyInflector::tableize('BlogPost') // => blog_posts
     * </code>
     * @param string $str
     * @return string
     */
    public static function tableize($str) {
        $str = self::pluralize($str);
        return self::underscore($str);
    }

    /**
     * Create name valid to be a part of controller name from snake_case string.
     * This method does not change plural/singular form of nouns.
     *
     * <code>
     * TipyInflector::controllerize('blog_post') // => BlogPost
     * TipyInflector::controllerize('blog_posts') // => BlogPosts
     * </code>
     * @param string $str
     * @return string
     */
    public static function controllerize($str) {
        $str = strtolower($str);
        $str = str_replace("_", " ", $str);
        $str = ucwords($str);
        $str = str_replace(" ", "", $str);
        return $str;
    }

}
