<?php

class Metrodi_Promise {
	public $thing;
	public $args;

	public function __construct($thing, $args=array()) {
		$this->thing = $thing;
		$this->args  = $args;
	}

	public function __invoke() {
		if (!count($this->args)) {
			return _make($this->thing);
		} else {
			return call_user_func_array('_make', $args);
		}
	}

	public function __call($name, $args) {
		return call_user_func_array( array($this(), $name), $args);
	}

	public function __get($key) {
		return $this()->{$key};
	}

	public function __set($key, $val) {
		$this()->{$key} = $val;
	}
}
