<?php
class SeasonModelTest extends PHPUnit_Framework_TestCase {
    public function testSeasonCreation() {
    	\bmtmgr\Model::connect();
    	$s = new \bmtmgr\Season(null, 'foobar', true);

    	$this->assertEquals($s->id, null);
    	$this->assertEquals($s->_is_new, true);
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
    	\bmtmgr\Model::connect();
    	$s = new \bmtmgr\Season(null, 'second', false);
    	$s->save();

    	$s2 = \bmtmgr\Season::by_id($s->id);
    	$this->assertEquals($s2->id, $s->id);
    	$this->assertEquals($s2->name, 'second');
    	$this->assertEquals($s2->visible, false);
    	$this->assertEquals($s2->_is_new, false);
	}

    /**
     * @expectedException \bmtmgr\utils\DuplicateEntryException
     */
    public function testSeasonDuplicates() {
    	\bmtmgr\Model::connect();
    	$s = new \bmtmgr\Season(null, 'dup', false);
    	$s->save();

		$s2 = new \bmtmgr\Season(null, 'dup', true);
		$s2->save();
	}
}