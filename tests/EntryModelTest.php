<?php
namespace bmtmgr;

class EntryModelTest extends \PHPUnit_Framework_TestCase {
    public function testEntryCreation() {
    	Model::connect();

        $club1 = User::create('entry_test1', 'entry_test1', 'entry_test1@aufschlagwechsel.de', []);
        $club1->save();
        $club2 = User::create('entry_test2', 'entry_test2', 'entry_test2@aufschlagwechsel.de', []);
        $club2->save();

    	$s = Season::create('foobar', true);

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

}