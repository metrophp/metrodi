<?php

include_once(__DIR__.'/../../container.php');
include_once(__DIR__.'/../../proto.php');

class Metrodi_Tests_Container extends PHPUnit_Framework_TestCase { 

	/**
	 * Ensure you can pass any arguments during 
	 * object creation time
	 */
	public function test_create_with_custom_args() {
		_didef('dummyobj', 'tests/dummyobj.txt', 'A', 'B');
		$obj = _make('dummyobj', 'C', 'D');
		$this->assertEquals( 'C', $obj->request );
		$this->assertEquals( 'D', $obj->response );
	}

	/**
	 * Ensure you can pass any arguments during 
	 * object creation time
	 */
	public function test_create_new_returns_clone() {
		_didef('dummyobj', 'tests/dummyobj.txt', 'A', 'B');
		$obj  = _make('dummyobj');
		$obj2 = _makeNew('dummyobj');
		$this->assertEquals( $obj, $obj2 );
		$this->assertNotSame( $obj, $obj2 );
	}

	/**
	 * Ensure same arguments get cached the same way
	 * object creation time
	 */
	public function test_same_arguments_cache_the_same() {
		_didef('dummyobj', 'tests/dummyobj.txt', 'A', 'B');
		$obj  = _make('dummyobj');
		$obj2 = _makeNew('dummyobj', 'A', 'B');
		$this->assertEquals( $obj, $obj2 );
		$this->assertNotSame( $obj, $obj2 );
	}

	/**
	 */
	public function test_create_a_promise() {
		_didef('dummyobj', 'tests/dummyobj.txt', 'A', 'B');
		$obj = _makePromise('dummyobj');
		$this->assertEquals( 'metrodi_promise', strtolower( get_class($obj) ) );
		$ret = $obj->testphase('', '');
		$this->assertTrue( $ret );
	}

	/**
	 */
	public function test_create_a_promise_with_args() {
		_didef('dummyobj', 'tests/dummyobj.txt', 'A', 'B');
		$obj = _makePromise('dummyobj');
		$this->assertEquals( 'metrodi_promise', strtolower( get_class($obj) ) );
		$this->assertEquals( 'A', $obj->request );
		$this->assertEquals( 'B', $obj->response );
	}

	/**
	 */
	public function test_undefined_things_yeild_proto() {
		$obj = _make('undef');
		$this->assertEquals( 'metrodi_proto', strtolower( get_class($obj) ) );
	}

	/**
	 */
	public function test_thing_defined_as_object() {
		$fake = (object)array();
		_didef('myfake', $fake);
		$obj = _make('myfake');
		$this->assertEquals( 'stdclass', strtolower( get_class($obj) ) );
	}

	/**
	 */
	public function test_global_get_and_set() {
		$cont = Metrodi_Container::getContainer();
		$test = $cont->get('flaga', 'foobar');
		$this->assertEquals('foobar', $test);

		_set('flagb', 5);

		$this->assertEquals(5, _get('flagb'));
	}

	/**
	 */
	public function test_load_object_by_classname() {
		_didef('dummyobj', 'Metrodi_Tests_Container');
		//must add empty array for PHPUnit constructor
		$obj = _make('dummyobj', array());
		$this->assertEquals( 'Metrodi_Tests_Container', get_class($obj) );
	}
}
