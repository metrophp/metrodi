metrodi
=======

Small, smart dependency injector

tl;dr
========
  - Doesn't use class types
  - Doesn't rely on autoloading
  - Checks parameter names first
  - Does actually use class types, but only after checking parameter names
  - Auto injects any public class variable ending in 'Service'

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
	$cart = _make('shoppingCartService');
	echo( get_class($cart) );  //  Metrodi_Proto

	_didef('shoppingCartService', 'path/to/my/cart.php');
	$cart = _make('shoppingCartService');
	echo( get_class($cart) );  //  Path_To_My_Cart
```


Constructor Injection
====================
When an object is created with \_make, the injector will inspect constructor arguments and find any defined things with the same name and pass them.  Why not use class types?  Well, imagine trying to switch an actual object implementation.  You would have to change every file that has the old class type hint, or you would have to create a library full of interfaces in order to get ready for any potential switch in the future.  

Some other languages don't have the benefit of being typeless.  Using the name of the variable instead of the type hint (while still falling back to the type hint) allows for the most rapid prototyping and the most flexible changing of dependency implementations down the line.

```php
	_didef('request',  '\Top\Quality\Request');
	_didef('response', '\Top\Quality\Response');

	class MyController {

		public function __construct($request, $response) {
			echo get_class($request);  // 'Top\Quality\Request';
		}
	}
```


Passing Parameters
==================
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

Singletons
==========
Singletons and new objects can both be access with \_make() but it depends on how you use \_make() with \_didef().

```php
   _didef('singletonService', '\ns\locator\class', 'arg1', 'arg2');

   //later
   $ss1 = _make('singletonService');

   //same reference
   $ss2 = _make('singletonService');
```

If your object is not inherently a service and needs new constructor params everytime you make it, you can pass
constructor params for every \_make() call.  Each unique combinations of parameters will be hashed and the resulting
object will be cached and return on subsequent calls to \_make()

```php
   _didef('user', '\ns\locator\class');

   //later
   $u1 = _make('user', 100);

   //new object
   $u2 = _make('user', 200);
```

You can combine both methods by supplying parameters to the \_didef() which will be combined and used as defaults to
subsequent \_make() calls

```php
   _didef('log', '\ns\locator\class', '/tmp/out.log');

   //later
   $mainLog    = _make('log');
   $specialLog = _make('log', '/var/log/special.log');
```

The \_makeNew() function will always return a new instance of a class definition regardless of parameters passed.  It simply
bypasses any instance cache and proceeds to make a new object the same way it does the very first time \_make() is called.

```php

   _didef('user', '\ns\user');

   //later
   $user1 = _make('user');
   $user2 = _makeNew('user'); //different instance
```


Closures
=========
Closures or any callbacks can be passed as the definition of any things as of 3.1.0.

```php
    _didef('connection', function($c) {
       return new \Ns\Connection($c['username'], $c['password']);
    });

   $c = _make('connection', array('bob', 'secret');
```

The result of the anonymous function will be return from \_make instead of the function object.  This behavior defaults
to behaving like a singleton where repeated calls to \_make('connection') will return the same object.

To get a new reference and have the anonymous function invoked again, use \_makeNew()

```php
    _didef('connection', function($c) {
        return new \Ns\Connection($c['username'], $c['password']);
    });

    $c  = _make('connection', array('bob', 'secret');
    $c2 = _makeNew('connection', array('alice', 'secret2');
```


Service Providers
=================
When you are making a thing from a class that defines public class variables and one of those variable names ends in
'Service', the dependency injector will automatically inject a lazy loading object for that service onto the object's variable.


```php
    class NullMailer {
        public function send() {
            print "Sending...\n";
        }
    }

    class Controller {
        public $emailService;
    }

    _didef('controller', 'Controller');
    _didef('emailService', new NullMailer());

    $c  = _make('controller');
    $c->emailService->send();
```

