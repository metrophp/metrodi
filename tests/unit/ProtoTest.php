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

		$this->assertEquals(5, $this->proto->get('undef', 5) );
	}

	/**
	 */
	public function test_magic_call() {
		_set('env', 'dev');
		ob_start();
		$this->proto->methodCall();
		$output = ob_get_contents().ob_end_clean();

		$this->assertEquals(0,
			strpos($output, 'Called [methodCall] against proto object of type: thing')
		);
	}

	public function test_to_string() {

		$this->assertEquals(
			'Proto object of type: thing'.PHP_EOL,
			$this->proto.''
		);
	}

}
