<?php

class UtilsTest extends PHPUnit_Framework_TestCase {
    public function test_sanitize_filename() {
    	$this->assertEquals(\bmtmgr\utils\sanitize_filename('a"bc""'), 'abc');
    	$this->assertEquals(\bmtmgr\utils\sanitize_filename("x\n"), 'x');
    	$this->assertEquals(\bmtmgr\utils\sanitize_filename('Düßeldorf 2015.dat'), 'Düßeldorf 2015.dat');
    }
}