<?php
namespace bmtmgr;

class SeasonModelTest extends \PHPUnit_Framework_TestCase {
    public function testSeasonCreation() {
    	Model::connect();
    	$s = Season::create('foobar', true, 'http://example.org/foo');

    	$this->assertEquals($s->id, null);
    	$this->assertEquals($s->_is_new, true);
        $this->assertEquals($s->baseurl, 'http://example.org/foo');
    	$this->assertEquals($s->name, 'foobar');
    	$this->assertEquals($s->visible, true);
    	$s->save();

    	$id = $s->id;
    	$this->assertNotEquals($s->id, null);
    	$this->assertEquals($s->_is_new, false);
    	$this->assertEquals($s->name, 'foobar');
    	$this->assertEquals($s->visible, true);
    	$s->save();

    	$this->assertEquals($s->id, $id);
    	$this->assertEquals($s->_is_new, false);
    }

    public function testSeasonFind() {
    	Model::connect();
    	$s = Season::create('second', false, 'http://example.org/foo2');
    	$s->save();

    	$s2 = Season::by_id($s->id);
    	$this->assertEquals($s2->id, $s->id);
    	$this->assertEquals($s2->name, 'second');
    	$this->assertEquals($s2->visible, false);
    	$this->assertEquals($s2->_is_new, false);
        $this->assertEquals($s->baseurl, 'http://example.org/foo2');
	}

    /**
     * @expectedException \bmtmgr\utils\DuplicateEntryException
     */
    public function testSeasonDuplicates() {
    	Model::connect();
    	$s = Season::create('dup', false, 'http://example.org/foo3');
    	$s->save();

		$s2 = Season::create('dup', true, 'http://example.org/foo4');
		$s2->save();
	}
}