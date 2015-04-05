<?php

class BasicTest extends PHPUnit_Framework_TestCase {
    public function testTestConfig() {
    	$this->assertEquals(\bmtmgr\config\get('test_force_init', '(unset)'), true);
    }

    public function testDb() {
    	\bmtmgr\Model::connect();
    }
}