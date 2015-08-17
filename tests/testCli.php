<?php

require_once 'autoload.php';

class TestCli extends TipyTestSuite {

    public function test() {
        $this->assertThrown('NoMethodException', "Call to undefined method TipyCli::lemon()", function () {
            TipyCli::lemon();
        });
    }

    public function testColors() {
        if (posix_isatty(STDOUT)) {
            $this->assertEqual(TipyCli::black('text'), "\033[0;30mtext\033[0m");
            $this->assertEqual(TipyCli::red('text'), "\033[0;31mtext\033[0m");
            $this->assertEqual(TipyCli::green('text'), "\033[0;32mtext\033[0m");
            $this->assertEqual(TipyCli::brown('text'), "\033[0;33mtext\033[0m");
            $this->assertEqual(TipyCli::blue('text'), "\033[0;34mtext\033[0m");
            $this->assertEqual(TipyCli::purple('text'), "\033[0;35mtext\033[0m");
            $this->assertEqual(TipyCli::cyan('text'), "\033[0;36mtext\033[0m");
            $this->assertEqual(TipyCli::lightGray('text'), "\033[0;37mtext\033[0m");
            $this->assertEqual(TipyCli::darkGray('text'), "\033[1;30mtext\033[0m");
            $this->assertEqual(TipyCli::lightRed('text'), "\033[1;31mtext\033[0m");
            $this->assertEqual(TipyCli::lightGreen('text'), "\033[1;32mtext\033[0m");
            $this->assertEqual(TipyCli::yellow('text'), "\033[1;33mtext\033[0m");
            $this->assertEqual(TipyCli::lightBlue('text'), "\033[1;34mtext\033[0m");
            $this->assertEqual(TipyCli::lightPurple('text'), "\033[1;35mtext\033[0m");
            $this->assertEqual(TipyCli::lightCyan('text'), "\033[1;36mtext\033[0m");
            $this->assertEqual(TipyCli::white('text'), "\033[1;37mtext\033[0m");
        } else {
            $this->assertEqual(TipyCli::black('text'), "text");
            $this->assertEqual(TipyCli::red('text'), "text");
            $this->assertEqual(TipyCli::green('text'), "text");
            $this->assertEqual(TipyCli::brown('text'), "text");
            $this->assertEqual(TipyCli::blue('text'), "text");
            $this->assertEqual(TipyCli::purple('text'), "text");
            $this->assertEqual(TipyCli::cyan('text'), "text");
            $this->assertEqual(TipyCli::lightGray('text'), "text");
            $this->assertEqual(TipyCli::darkGray('text'), "text");
            $this->assertEqual(TipyCli::lightRed('text'), "text");
            $this->assertEqual(TipyCli::lightGreen('text'), "text");
            $this->assertEqual(TipyCli::yellow('text'), "text");
            $this->assertEqual(TipyCli::lightBlue('text'), "text");
            $this->assertEqual(TipyCli::lightPurple('text'), "text");
            $this->assertEqual(TipyCli::lightCyan('text'), "text");
            $this->assertEqual(TipyCli::white('text'), "text");
        }
    }

}
