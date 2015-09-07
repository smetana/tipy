<?php

require_once 'autoload.php';

class AssertionsTest extends TipyTestCase {
    
    public function testEqual() {
        $this->assertEqual(1, 1);
        $this->assertEqual("1", 1);
        $this->assertEqual(true, 1);
        $this->assertEqual(new DateTime("2015-08-26"), new DateTime("2015-08-26"));
    }

    public function testSame() {
        $this->assertIdentical(1, 1);
        $this->assertIdentical("1", "1");
        $this->assertIdentical(false, false);
    }

    public function testNotEqual() {
        $this->assertNotEqual(1, "2");
        $this->assertNotEqual(1, 2);
        $this->assertNotEqual(1, false);
        $this->assertNotEqual(new DateTime("2015-08-26"), new DateTime("2015-08-25"));
    }

    public function testNull() {
        $this->assertNull(null);
    }

    public function testNotNull() {
        $this->assertNotNull(0);
        $this->assertNotNull('');
        $this->assertNotNull(false);
    }

    public function testTrue() {
        $this->assertTrue(true);
    }

    public function testFalse() {
        $this->assertFalse(false);
    }

    public function testThrown() {
        $this->assertThrown("Exception", "Bang!", function() {
            throw new Exception("Bang!");
        });
    }

    public function testThrownRegexp() {
        $this->assertThrownRegexp("Exception", "/really/", function() {
            throw new Exception("Bang! It is a really Bang!");
        });
    }

}
