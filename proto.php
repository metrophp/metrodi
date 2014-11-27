<?php

class Metrodi_Proto {

	protected $thing;

	public function __construct($thing) {
		$this->thing = $thing;
	}

	/**
	 * Intercept all function calls so there are no stopping errors.
	 * in DEV mode (_set('env', 'dev')) a trace will be emitted.
	 */
	public function __call($name, $args) {
		//only show proto messages in dev mode
		if (_get('env') != 'dev') {
			return $this;
		}
		$bt = debug_backtrace();
		if (!isset($bt[0]) ||
		    !array_key_exists('line', $bt[0])) {
			return $this;
		}
		$line = $bt[0]['line'];
		$file = $bt[0]['file'];
		$bt = null;
		$parts = explode(DIRECTORY_SEPARATOR, $file);
		$fname = array_pop($parts);
		$file = array_pop($parts).DIRECTORY_SEPARATOR.$fname;
		echo("Called [".$name."] against proto object of type: ".$this->thing." from: ".$file." (".$line.").\n");
		return $this;
	}

	public function __set($key, $value) {
		$this->{$key} = $value;
	}

	public function __toString() {
		return "Proto object of type: ".$this->thing.PHP_EOL;
	}
}
