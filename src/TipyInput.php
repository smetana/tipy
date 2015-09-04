<?php
/**
 * TipyInput
 *
 * @package tipy
 */

/**
 * Represents input data
 *
 * By default it is a TipyIOWrapper around $_REQUEST superglobal
 *
 * <code>
 * class MyController extends TipyController {
 *     public function index() {
 *         $firstName = $this->in->get('first_name');
 *         // or by shortcut
 *         $lastName = $this->in('last_name');
 *     }
 * }
 * </code>
 */
class TipyInput extends TipyIOWrapper {

    /**
     * Construct TipyInput instance from $_REQUEST
     */
    public function __construct() {
        parent::__construct();
        $this->bind($_REQUEST);
    }

}
