<?php
/**
 * TipyCliSession
 *
 * @package tipy
 */

/**
 * Session mock for CLI_MODE
 */
class TipyCliSession extends TipyIOWrapper {

    /**
     * Close session and clear all session data
     */
    public function close() {
        $this->ioArray = [];
    }
}


