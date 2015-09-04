<?php
/**
 * TipyOutput
 *
 * @package tipy
 */

/**
 * Action output data
 *
 * These values will be assigned to {@link TipyView} template on page rendering
 *
 * Usage:
 * <code>
 * class MyController extends TipyController {
 *     public function index() {
 *         $this->out->set('firstName', 'John');
 *         // or by shortcut
 *         $this->out('lastName', 'Doe');
 *     }
 * }
 * </code>
 *
 * @see TipyView
 */
class TipyOutput extends TipyIOWrapper {
}
