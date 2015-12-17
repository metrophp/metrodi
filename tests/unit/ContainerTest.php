<?php

include_once(__DIR__.'/../../container.php');
include_once(__DIR__.'/../../proto.php');

class Metrodi_Tests_Container extends PHPUnit_Framework_TestCase { 

	/**
	 */
	public function test_cache_returns_same_object() {
		_didef('cacheobj', 'tests/dummyobj.txt', 'A', 'B');
		$obj1 = _make('dummyobj');
		$obj2 = _make('dummyobj');
		$this->assertSame( $obj1, $obj2 );
	}

	/**
	 */
	public function test_invokables_as_argumens() {
		_didef('dummyobj', 'tests/dummyobj.txt', 'A');
		$obj = _makeNew('dummyobj', 'A', function() { return 'B';});
		$this->assertEquals( 'A', $obj->request );
		$this->assertEquals( 'B', $obj->response );
	}

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

//		$this->assertEquals( $obj, $obj2 );
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
		$this->assertNotSame( $obj, $obj2 );
	}

	/**
	 */
	public function test_instances_are_returned_as_defined() {
		$inst = (object)array();
		$inst->a = 'A';
		$inst->b = 'B';
		_didef('instobj', $inst);
		$obj  = _make('instobj');
		$this->assertSame( $obj, $inst );
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
	public function test_repeated_loadAndCache_to_make_return_same_reference() {
		$container = new Metrodi_Container('.', array('.', '../../') );
		$obj1 = $container->loadAndCache('tests/dummyobj.txt', 'tests/dummyobj.txt');
		$obj2 = $container->loadAndCache('tests/dummyobj.txt', 'tests/dummyobj.txt');
		$this->assertSame( $obj1, $obj2 );
	}


	/**
	 */
	public function test_undefined_things_yeild_proto() {
		$obj = _make('undef');
		$this->assertEquals( 'metrodi_proto', strtolower( get_class($obj) ) );
	}

	/**
	 */
	public function test_broken_file_definitions_return_proto() {
		_didef('broken', 'q.php');
		$obj = _make('broken');
		$this->assertEquals( 'metrodi_proto', strtolower( get_class($obj) ) );
	}


	/**
	 */
	 /*
	public function test_thing_defined_as_object() {
		$fake = (object)array();
		_didef('myfake', $fake);
		$obj = _make('myfake');
		$this->assertEquals( 'stdclass', strtolower( get_class($obj) ) );
	}
	*/

	/**
	 */
	public function test_anonymous_function_definition() {
		$anon = function() { return "StringA"; };
		_didef('myanon', $anon);
		$obj = _make('myanon');
		$this->assertEquals( 'StringA', $obj );
	}

	public function test_anonymous_function_with_args() {
		$anon = function($name) { return "Hello, ".$name; };
		_didef('myanonwitharg', $anon);
		$obj = _make('myanonwitharg', 'World');
		$this->assertEquals( 'Hello, World', $obj );
	}

	/**
	 * Test that _make always returns a reference to the same object and
	 * makeNew returns a new object
	 */
	public function test_anonymous_function_singleton() {
		$anon = function() { $x = (object)array();  $x->var='used'; return $x;};
		_didef('myanonsingleton', $anon);
		$obj  = _make('myanonsingleton');
		$obj->var = 'used twice';
		$obj2 = _makeNew('myanonsingleton');
		$obj3 = _make('myanonsingleton');

		$this->assertEquals( 'used', $obj2->var );
		$this->assertSame( $obj, $obj3 );
	}

	/**
	 * Test that _make always returns a reference to the same object and
	 * makeNew returns a new object
	 */
	public function test_anonymous_function_singleton_with_args() {
		$anon = function($a) { $x = (object)array();  $x->var=$a; return $x;};
		_didef('myanonsingleton', $anon);
		$obj  = _make('myanonsingleton', 'A');
		$obj->var = 'B';
		$obj2 = _makeNew('myanonsingleton', 'C');
		$obj3 = _make('myanonsingleton', 'A');

		$this->assertEquals( 'C', $obj2->var );
		$this->assertEquals( 'B', $obj3->var );
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
		_didef('testcontainer', 'Metrodi_Tests_Container');
		$obj = _make('testcontainer');
		$this->assertEquals( 'Metrodi_Tests_Container', get_class($obj) );
	}
}
