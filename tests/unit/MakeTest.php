<?php

include_once(__DIR__.'/../../container.php');
include_once(__DIR__.'/../../proto.php');

class Metrodi_Tests_Make extends PHPUnit_Framework_TestCase {


	public function test_makenew_returns_proto_when_undefined() {
		$x = _makeNew('FoobarClass00', ['x'=>'y']);
		$this->assertEquals(
			'metrodi_proto',
			strtolower(get_class($x))
		);
	}

	public function test_make_returns_proto_when_undefined() {
		$x = _make('FoobarClass01', ['x'=>'y']);
		$this->assertEquals(
			'metrodi_proto',
			strtolower(get_class($x))
		);
	}

}
