<?php
/**
 * TipyIOWrapper
 *
 * @package tipy
 */

/**
 * Base class to wrap input/output superglobals for sanitation/validation
 *
 * For example if you want extra XSS protection on input or output
 * you may use controller's executeBefore() or executeAfter() hooks:
 *
 * <code>
 * class MyController extends TipyController {
 *     public function executeAfter() {
 *         foreach ($this->out->getAll() as $key => $value) {
 *             $this->out->set($key, htmlspecialchars($value));
 *         }
 *     }
 * }
 * </code>
 */
class TipyIOWrapper {

    /**
     * This array represents superglobal arrays like $_GET, $_POST, etc...
     */
    private $map;

    /**
     * Construct empty TipyIOWrapper instance
     */
    public function __construct() {
        $this->map = [];
    }

    /**
     * Specify array to wrap in TipyIOWrapper
     *
     * This array should be a hash with string keys.
     * This may be superglobal array like $_GET, $_POST, etc...
     *
     * @param array $map
     */
    public function bind(array $map) {
        $this->map = $map;
    }

    /**
     * Return value from internal hash by key name
     *
     * If key does not exist return defaultValue
     *
     * @param string $key
     * @param mixed $defaultValue
     * @return mixed
     */
    public function get($key, $defaultValue = null) {
        return array_key_exists($key, $this->map) ? $this->map[$key] : $defaultValue;
    }

    /**
     *
     * @param string $key
     * @param mixed $value
     */
    public function set($key, $value) {
        $this->map[$key] = $value;
    }

    /**
     * Return all data from internal map
     */
    public function getAll() {
        return $this->map;
    }

    /**
     * Clear all data in internal map
     */
    public function clear() {
        $this->map = [];
    }
}
