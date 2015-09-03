<?php
/**
 * TipyEnv
 *
 * @package tipy
 */

/**
 * Access environment variables
 *
 * Usage:
 * <code>
 * class MyController extends TipyController {
 *     public function index() {
 *         $path = $this->env->get('PATH');
 *         // ...
 *     }
 * }
 * </code>
 */
class TipyEnv {

    /**
     * @param String $key
     * @return string
     */
    public function get($key) {
        return getenv($key);
    }

}
