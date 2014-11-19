metrodi
=======

Small, smart dependency injector

tl;dr
========
  - Doesn't use class types
  - Doesn't rely on autoloading
  - Checks parameter names first
  - Does actually use class types, but only after checking parameter names
  - Auto injects any public member variable ending in 'Service'

Setup
=====
Metro DI can be used by creating an object or as a singleton with globally namespaced functions.

```php
	$cont = Metrodi_Container::getContainer();
	$cont->set('flaga', true);
	$cont->didef('logService', 'path/to/log.php', 'arg1', 'arg2', 'arg3');
	$log = $cont->make('logService');
```

With global functions it looks like this:

```php
	_set('flaga', true);
	_didef('logService', 'path/to/log.php', 'arg1', 'arg2', 'arg3');
	$log = _make('logService');
```

Things
=====
In Metro DI you name your dependencies with the DI def function.  An undefined thing will return a prototype object that logs all method calls used against it via the magic \_\_call.
```php
	$cart = _get('shoppingCartService');
	echo( get_class($cart) );  //  Metrodi_Proto

	_didef('shoppingCartService', 'path/to/my/cart.php');
	$cart = _get('shoppingCartService');
	echo( get_class($cart) );  //  Path_To_My_Cart
```


Constructor Injection
====================
When an object is created with \_make, the injector will inspect constructor arguments and find any defined things with the same name and pass them.  Why not use class types?  Well, imagine trying to switch an actual object implementation.  You would have to change every file that has the old class type hint, or you would have to create a library full of interfaces in order to get ready for any potential switch in the future.  

Some other languages don't have the benefit of being typeless.  Using the name of the variable instead of the type hint (while still falling back to the type hint) allows for the most rapid prototyping and the most flexible changing of dependency implementations down the line.

```php
	class MyController {

		public function __construct($request, $response) {
			echo get_class($request);  // whatever you setup in _didef('request', ....);
		}
	}
```


Passing Parameters
====================
You can pass parameters both when defining an object and when making an object.

```php
	namespace org\my\cart\service\abstract\concrete\interface;
	class Cart {
		public function __construct($idUser, $listItems, $timestamp=NULL) {
			$this->idUser    = $idUser;
			$this->listItems = $listItems;
			$this->timestamp = $timestamp;
		}
	}

	_didef('shoppingCartService', '\org\my\cart\service\abstract\concrete\interface\Cart', 'A', 'B');
	$cart1 = _make('shoppingCartService');
	echo $cart1->idUser;     // 'A'
	echo $cart1->listItems;  // 'B'
	echo $cart1->timestamp;  // null

	$cart2 = _make('shoppingCartService', 'C', 'D', time());
	echo $cart2->idUser;     // 'C'
	echo $cart2->listItems;  // 'D'
	echo $cart2->timestamp;  // 1234567890  (YMMV)
```
