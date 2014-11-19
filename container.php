<?php
include_once(dirname(__FILE__).'/promise.php');
include_once(dirname(__FILE__).'/proto.php');

class Metrodi_Container {

	public $thingList    = array();
	public $thingArgList = array();
	public $varList      = array();
	public $objectCache  = array();
	public $searchDirs   = array('src', 'local', '.');
	public $rootDir      = '';

	static $container    = NULL;

	public function __construct($root='.', $dirs=NULL) {
		// register shutdown functions execute in a different directory.
		// we need to set include path. for exception lifecycle
		// root/vendor/metrophp/metrodi/container.php
		set_include_path(
			get_include_path().':'.dirname(dirname(dirname(dirname(__FILE__))))
		);

		$this->rootDir = $root;
		if (!empty($dirs)) {
			$this->searchDirs = (array)$dirs;
		}
		self::$container = $this;
	}

	static public function &getContainer() {
		if (self::$container == NULL) {
			self::$container = new Metrodi_Container();
		}
		return self::$container;
	}

	/**
	 * Define a file as a thing.
	 * Any extra arguments are saved and used as constructor arguments
	 */
	public function didef($thing, $file) {
		$this->thingList[$thing] = $file;

		$args = func_get_args();
		//remove 2 known params
		array_shift($args);
		array_shift($args);
		if (count($args)) {
			$this->thingArgList[$thing] = $args;
		}
	}

	/**
	 * Return a Metrodi_Promise, which returns
	 * the desired instance when __invoked()
	 */
	public function makePromise($thing) {
		$args = func_get_args();
		array_shift($args);
		return new Metrodi_Promise($thing, $args);
	}

	/**
	 * Return a defined thing or an empty object (Metrodi_Proto)
	 * @return object  defined thing or empty object (Metrodi_Proto)
	 */
	public function & make($thing) {
		if (!isset($this->thingList[$thing])) {
			$this->thingList[$thing] = 'StdClass';
		}
		if (is_object($this->thingList[$thing])) {
			return $this->thingList[$thing];
		}

		$filesep = '/';
		$objList = array();
		$file = $this->thingList[$thing];

		$args = func_get_args();
		array_shift($args);

		if (!count($args) && isset($this->thingArgList[$thing])) {
			$args = $this->thingArgList[$thing];
		}
		if (!count($args)) {
			$args = NULL;
			$cachekey = $file.':'.$thing;
		} else {
			$cachekey = $file.':'.$thing.':'.sha1(serialize($args));
		}

		if (!$this->loadAndCache($file, $cachekey, $args))
			$this->objectCache[$cachekey] = new Metrodi_Proto($thing);

		return $this->objectCache[$cachekey];
	}

	/**
	 * Return a clone (shallow copy) of a defined thing or an empty object (Metrodi_Proto)
	 * @return object  clone of a defined thing or empty object (Metrodi_Proto)
	 */
	public function makeNew($thing) {
		$args = func_get_args();
		if (count($args) <= 1) {
			return clone $this->make($thing);
		} else {
			return clone call_user_func_array(array($this, 'make'), $args);
		}
	}

	/**
	 * load a file from local/$file or src/$file.
	 * Save object to $this->objectCache[$cachekey]
	 *
	 * @return  Boolean True if file was loaded and saved
	 */
	public function loadAndCache($file, $cachekey, $args=NULL) {
		if (isset($this->objectCache[$cachekey])) {
			return TRUE;
		}
		//if something is undefined, its 'file' in the thingList is set to StdClass
		if ($file === 'StdClass') return FALSE;

		//try file loading only if it looks like a file
		$className = $this->tryFileLoading($file);

		if (is_array($args) && class_exists('ReflectionClass', false)) {
			$refl = new ReflectionClass($className);
			try {
				//invoke lazy loading promises
				foreach ($args as $_argk => $_argv) {
					if (is_object($_argv) && method_exists($_argv, '__invoke')) {
						$args[ $_argk ] = $_argv();
					}
				}
				$_x = $refl->newInstanceArgs($args);
			} catch (ReflectionException $e) {
				$_x = $refl->newInstance();
			}
		} else {
			$_x = new $className;
		}
		$this->attachServices($_x);

		$this->objectCache[$cachekey] = $_x;
//		$_x = null;
		return TRUE;
	}

	public function tryFileLoading($file) {
		//file is actually a class name or namespace
		if (strrpos($file, '.') === FALSE) {
			return $file;
		}

		$filesep = '/';

		$loaded = FALSE;
		foreach ($this->searchDirs as $_dir) {
			if(file_exists($_dir.$filesep.$file)) {
				if(include_once($file)) {
					$loaded = TRUE;
					break;
				}
			}
		}
		if (!$loaded) {
			return FALSE;
		}
		return $this->formatClassName($file);
	}

	/**
	 * Set a DI promise object on every
	 * class var that ends with 'Service'
	 */
	public function attachServices($obj) {
		$args = get_class_vars( get_class($obj) );
		foreach ($args as $_k=>$_v) {
			if (substr($_k, -7) == 'Service') {
				$obj->$_k = _makePromise($_k);
			}
		}
	}

	public function set($key, $val) {
		$this->varList[$key] = $val;
	}

	public function get($key, $default=NULL) {
		if (!isset($this->varList[$key]))
			return $default;

		return $this->varList[$key];
	}

	public function formatClassName($filedidef) {
		$filesep = '/';
		$className = substr($filedidef, 0, strrpos($filedidef, '.'));
		$didefList  = explode($filesep, $className);
		$className = '';
		foreach ($didefList as $_n) {
			if ($className) $className .= '_';
			$className .= ucfirst($_n);
		}
		$className = str_replace('-', '', $className);
		return $className;
	}

}

function _didef($thing, $file) {
	$a = Metrodi_Container::getContainer();
	$args = func_get_args();
	if (count($args) <= 2) {
		return $a->didef($thing, $file);
	} else {
		return call_user_func_array(array($a, 'didef'), $args);
	}
}

function _make($thing) {
	$a = Metrodi_Container::getContainer();
	$args = func_get_args();
	if (count($args) <= 1) {
		return $a->make($thing);
	} else {
		return call_user_func_array(array($a, 'make'), $args);
	}
	return $a->make($thing);
}

function _makeNew($thing) {
	$a = Metrodi_Container::getContainer();
	$args = func_get_args();
	if (count($args) <= 1) {
		return  $a->makeNew($thing);
	} else {
		return call_user_func_array(array($a, 'makeNew'), $args);
	}
}

function _makePromise($thing) {
	$a = Metrodi_Container::getContainer();
	$args = func_get_args();
	if (count($args) <= 1) {
		return $a->makePromise($thing);
	} else {
		return call_user_func_array(array($a, 'makePromise'), $args);
	}
	return $a->makePromise($thing);
}

function _set($key, $val) {
	$a = Metrodi_Container::getContainer();
	return $a->set($key, $val);
}

function _get($key, $def=NULL) {
	$a = Metrodi_Container::getContainer();
	return $a->get($key, $def);
}
