<?php
/**
 * Session mock for CLI_MODE
 */
class TipyCliSession extends TipyIOWrapper {
    public function close() {
        $this->ioArray = [];
    }
}


