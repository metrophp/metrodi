<?php

include_once(__DIR__.'/../../container.php');
include_once(__DIR__.'/../../proto.php');

class Metrodi_Tests_Proto extends PHPUnit_Framework_TestCase {

	public function setUp() {
		$this->proto = new Metrodi_Proto('thing');
	}

	/**
	 */
	public function test_set_and_get() {
		$this->proto->set('x', 'y');

		$this->assertEquals( 'y', $this->proto->x );

		$this->proto->a =  'b';
		$this->assertEquals( 'b', $this->proto->get('a') );
	}
}
