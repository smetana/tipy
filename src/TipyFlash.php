<?php
/**
 * TipyFlash
 *
 * @package tipy
 */

/**
 * Send data between actions
 *
 * The flash concept is borrowed from Rails. It provides a way to pass
 * temporary primitive-types (Strings, Integers, Arrays) between actions.
 * Anything you place in the flash will be exposed to the next action.
 * This is a great way of doing notices and alerts after redirects.
 *
 * <code>
 * class BlogController extends TipyController {
 *     public function save() {
 *         // ...save post...
 *         $this->flash->set('Post successfully created');
 *         $this->redirect('/blog/index');
 *     }
 * }
 * </code>
 */
class TipyFlash {

    /**
     * Flash keeps its data in session
     * @internal
     */
    private $session;
    /**
     * currentMessage is available only or current action
     * and will be cleared on the very next action
     * @internal
     */
    private $currentMessage;

    /**
     * @param TipySession $session
     */
    public function __construct($session) {
        $this->session = $session;
        $this->currentMessage = $this->session->get('flashMessage');
        $this->session->set('flashMessage', null);
    }

    /**
     * @param mixed $message
     */
    public function set($message) {
        $this->session->set('flashMessage', $message);
    }

    /**
     * @return mixed
     */
    public function get() {
        return $this->currentMessage;
    }
}
