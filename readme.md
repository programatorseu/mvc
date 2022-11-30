# 1. PHP

There is really low-level barrier to entry if it comes to PHP

**Table of contents**

1. Arrays  & Associative Array

2. Functions & Lamba(Java:) ) 

3. Using function & superglobal to select current page

4. Code Organization 

5. PDO Connections
6. Pass config
7. Create Router
8. Form / Reqest Types and Routing 
9. Dynamic Inserts - PDO
10. Composer Autoloading 

11. Dependency Injection Container
12. Refactoring to Controller Class
13. Namespaces 




### 
   

### 1. Arrays

foremost to know something about map or mapping rules used in php - 

https://www.php.net/manual/en/function.array.php

```php
    $teams = [
        "Atlanta Hawks",
        "Boston Celtics",
        "Brooklyn Nets",
        "Charlotte Hornets",
        "Chicago Bulls",
        "Cleveland Cavaliers",
        "Dallas Mavericks",
        "Denver Nuggets",
        "Detroit Pistons",
      ];

```

```html
    <ul>
        <?php foreach ($teams as $team) : ?>
            <li><?= $team; ?></li>
        <?php endforeach; ?>
    </ul>
```

**associative array**

```php
    <?php
    $teams = [
        [
            "name" => "Los Angeles Lakers",
            "location" => "Los Angeles"
        ]
    ];

    ?>
    <ul>
        <?php foreach ($teams as $team) : ?>
            <li><?= $team['name'] . ' and location is : ' . $team['location']; ?></li>
        <?php endforeach; ?>
    </ul>

```



### 2.Functions & Lambda

```php
<?php
function filterByCity($teams, $city)
{

    $filtered = [];
    foreach ($teams as $team) {
        if ($team["location"] === $city) {
            $filtered[] = $team;
        }
    }
    return $filtered;
}

    <ul>
        <?php foreach (filterByCity($teams, "Los Angeles") as $team) : ?>
            <li><?= $team['name'] . ' and location is : ' . $team['location']; ?></li>
        <?php endforeach; ?>
    </ul>
```

refactor that function



```php
function filter($array, $index, $searchValue)
{

    $filtered = [];
    foreach ($array as $element) {
        if ($element[$index] === $searchValue) {
            $filtered[] = $element;
        }
    }
    return $filtered;
}


<?php foreach (filter($teams, "location", "Los Angeles") as $team) : ?>
```



- ability to create anynomous function that we can pass - lambda  -- step before actual `array_filter`

```php
<?php
function filter($array, $fn)
{

    $filtered = [];
    foreach ($array as $element) {
        if ($fn($element)) {
            $filtered[] = $element;
        }
    }
    return $filtered;
}

$filtered = filter($teams, function ($team) {
        return $team["location"] === "Cleveland";
    });
```

### 3.Using function & superglobal to select current page

```php
function dd($value)
{
    echo "<pre>";
    echo  var_dump($value);
    echo "</pre>";
    die();
}
function isCurrent($url)
{
    return $_SERVER['REQUEST_URI'] === $url;
}
```



```php
 <a href="/" class="
                        <?= isCurrent("/") ? 'current' : '' ?>
```

### 4. Code Organization

> **controllers\ **
>
> ​	index.php, contact.php
>
> **views\ **
>
> ​	index.view.php, contact.view.php

**router file : **

```php
<?php
$uri = parse_url($_SERVER['REQUEST_URI'])['path'];

$routes = [

    '/' => 'controllers/index.php',
    '/about' => 'controllers/about.php',
    '/contact' => 'controllers/contact.php'
];

function routing($uri, $routes)
{
    if (array_key_exists($uri, $routes)) {
        require $routes[$uri];
    } else {
        abort();
    }
}

function abort()
{
    http_response_code(404);
    require "views/404.view.php";
    die();
}
routing($uri, $routes);
```

### 5. Pdo connections 

a) make connection with PDO

 - think about using singleton pattern in case of connection

Make function will return PDO object 

```php
<?php
class Database
{

    public static function connect($config, $username = 'root',  $password = '')
    {
        $dsn = 'mysql:' . http_build_query($config, '', ';');
        try {
            return new PDO($dsn, $username, $password, [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
        } catch (PDOException $e) {
            die('Could not connect' . $e->getMessage());
        }
    }
}

```

b) `core/database/Query` 
-> we will inject PDO inside : constructor 
-> we will call selectAll method 

```php
<?php
class Query {
    protected $pdo;
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    public function selectAll($table)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$table}");
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_CLASS);
    }
}

```

C) core/bootstrap.php 

We will return connection 
Then from index we will just require that file and returned results hold in variable 

Bootstrap file :

```php
<?php
require 'Database/Database.php';
require 'Database/Query.php';
$config = require 'config.php';

return new Query(Database::connect($config, 'root', ''));

```

index.php file : 

```php
<?php
$db = require 'core/bootstrap.php';

$posts = $db->selectAll('posts');

```

—	

### 6. Pass Config

A) create dedicated config file
It will return array of config properties 

```
<?php
return [
    'host' => 'localhost',
    'port' => 3306,
    'dbname' => 'demo',
    'charset' => 'utf8mb4'
];

```

b) require config and call it in method in bootstrap : 

```php
$config = require 'config.php';

return new Query(Database::connect($config, 'root', ''));

```

—	

### 7. Make router 

`index.php` -> is our entry point . 
It will load up bootstrap file 
`Bootstrap.php `->  loading config and db connection 

>**core/**
>
>--**database**
>
>--`boostrap.php`
>
>-- `Request.php`
>
>-- `Router.php`
>
>**views/**
>
>**controller/**
>
>index.php
>
>routes.php

Request file 

```php
<?php
class Request
{
    public static function uri()
    {
        return ltrim(parse_url($_SERVER['REQUEST_URI'])['path'], '/');
    }
}

```

Create `core/Router.php`

Router.php file looks like :

`@load` - > load file containing route array (inject into class)

`@define` -> simply populate array

`@direct()` -> 

- take uri and based on passed uri find proper controller 
  If array_key_exists($uri , search through our routes)
  Return routes based on this array 

```php
<?php
class Router
{
    protected $routes = [];

    public static function load($file)
    {
        $router = new static;
        require $file;
        return $router;
    }
    public function define($routes)
    {
        $this->routes = $routes;
    }


    public function direct($uri)
    {
        if (array_key_exists($uri, $this->routes)) {
            return $this->routes[$uri];
        }
        $this->abort();
    }

    protected function abort()
    {
        http_response_code(404);
        require "views/404.view.php";
        die();
    }
}

```

Our index.php 

- loads up bootstrap file 
- include Router class  
- load routes file 
- call direct method with trimmed uri based on static call to `Request`

```php
<?php
$db = require 'core/bootstrap.php';

require Router::load('routes.php')->direct(Request::uri());

```



### 8. Form, Request Types and Routing



1. Class Request : 
   -> add method() for further code 

```php
<?php
class Request
{
    public static function uri()
    {
        return ltrim(parse_url($_SERVER['REQUEST_URI'])['path'], '/');
    }
    public static function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }
}

```

2. updates routes.php File specific for method type 

```
<?php 


$router->get('', 'controllers/index.php');
$router->get('about', 'controllers/about.php');
$router->get('contact', 'controllers/contact.php');

$router->post('names', 'controllers/add-name.php');


```

3. Router class  

1) array of get and post arrays 
2) create get and post methods for adding to specific arrays
3) update direct() method to accept method argument 

```php
<?php 
class Router {
	protected $routes = [
		'GET' => [],
		'POST' => []
	];

	public static function load($file) 
	{
		$router = new static;
		require $file;
		return $router;
	}

	public function get($uri, $controller) 
	{
		$this->routes['GET'][$uri] = $controller;
	}

	public function post($uri, $controller)
	{
		$this->routes['POST'][$uri] = $controller;
	}

	public function direct($uri, $requestType) 
	{
		if(array_key_exists($uri, $this->routes[$requestType])) {
			return $this->routes[$requestType][$uri];
		}
		throw new Exception('No route defined for this URI. ');
	}
}

```

Call from indeX:

```php
<?php
$db = require 'core/bootstrap.php';

require Router::load('routes.php')->direct(Request::uri(), Request::method());

```

—	

### 9. Dynamic Inserts with PDO

index.view.php:

```php
<?php require('partials/head.php'); ?>
	
	<h2>Submit name</h2>
	<form action="/names" method="post">
		<input type="text" name="name" />
		<button type="submit">Submit</button>
	</form>

<?php require('partials/footer.php'); ?>

```

Add to router exact path 
add-name.php  call insert function : 

```php
<?php
$app['database']->insert('posts', [
    'title' => $_POST['title']
]);

```

Query class `@insert` method : 

```php
	public function insert($table, $params) 
	{
			$sql = sprintf('insert into %s (%s) values(%s)',
			$table,
			implode(', ', array_keys($params)),
			':' . implode(', :', array_keys($params))	);

		try {
			$stmt = $this->pdo->prepare($sql);
			$stmt->execute($params);
		} catch(PDOException $e) {
			die($e->getMessage());
		}
	
	}
```

### 10. Composer Autoloading

We require more and more files in bootstrap 
We want to automatically load

Create composer.json - to autoload any class:

```json
{
	"autoload" : {
		"classmap" : [
			"./"
		]
	}
}
```

Run composer install
In index.php:

```php
<?php 
require 'vendor/autoload.php';
require 'core/bootstrap.php';
```

Now bootstrap looks like : 

```php
<?php
$app = [];
require __DIR__ . '/../utils/functions.php';
$app['config'] = require 'config.php';



$app['database'] = new Query(Database::connect($app['config'], 'root', ''));

```

—	

### 11. Dependency Injection container

Right now we store dependencies inside $app array 

Create `core/App.php`  - it will be basic dependency injection container 

We are going to have 2 methods there
`bind()`  & `get`

So  bootstrap.php file will look like : 

```php
<?php
require __DIR__ . '/../utils/functions.php';

App::bind('config', require 'config.php');
App::bind('database', new Query(
    Database::connect(App::get('config'), 'root', '')
));

```

App class: 

```php
<?php 
class App 
{
	protected static $registry = [];

	public static function bind($key, $value) {
		static::$registry[$key] = $value;
	}
	public static function get($key) {
		if(! array_key_exists($key, static::$registry)) {
			throw new Exception("No {$key} is bound to the container");
		}
		return static::$registry[$key];
	}
}

```

Then we just call it : 

```php
App::get('database')->insert('posts', [
    'title' => $_POST['title']
]);
```

— 

### 12. Refactoring to Controller Classes 

Controllers/PagesController.php 

Main tasks: 
-> receive request 
-> delegate ( ask db for records)
-> response 

```php
<?php
class PagesController
{
    public function home()
    {
        $posts = App::get('database')->selectAll('posts');
        return view('index');
    }
    public function about()
    {
        return view('about');
    }
    public function contact()
    {
        return view('contact');
    }
    public function posts()
    {
        App::get('database')->insert('posts', [
            "title" => $_POST['title']
        ]);
        return redirect("");
    }
}

```

about view() and redirect() method

we have created them in `utils/functions.php` file : 

```php
function view($name, $data = [])
{
	extract($data);
	return require "views/{$name}.view.php";
}

function redirect($path)
{
	header("Location: /{$path}");
}
```



Now update routes:
@-> separator of controller and method 

+ we change or remove controllers folder from the path : 

```php
<?php 


$router->get('', 'PagesController@home');
$router->get('about', 'PagesController@about');
$router->get('contact', 'PagesController@contact');

$router->post('names', 'PagesController@names');

```

to remind: 

our index.php looks like



```php
<?php
require 'vendor/autoload.php';
require 'core/bootstrap.php';

require Router::load('routes.php')->direct(Request::uri(), Request::method());

```

to remind : 

... -> spread operator turn array into strings 

```php
<?php 
var_dump(...explode('@', 'PagesController@home'));
// 2 strings PagesController and home 
```



Router

in direct() method: 

1. if array_key_exists  true

2. callAction()

```php
   public function direct($uri, $requestType)
    {
        if (array_key_exists($uri, $this->routes[$requestType])) {
            return $this->callAction(
                ...explode('@', $this->routes[$requestType][$uri])
            );
        }
        $this->abort();
    }
    protected function callAction($controller, $action)
    {
        $controller = new $controller;
        if (!method_exists($controller, $action)) {
            throw new Exception(
                "{$controller} does not respond to the {$action}"
            );
        }
        return $controller->$action();
    }

    protected function abort()
    {
        http_response_code(404);
        require "views/404.view.php";
        die();
    }
```





---

#### 12.Namespaces

to avoid collisions we will use namespace

it is like a folder we organize 

App -> top-level namespace

Controllers -> folder 



```php
<?php 
namespace App\Controllers;

class PagesController  

```



for App.php  :

```php
<?php 
namespace App\Core;
class App 

```

we will do it for Router and Request file 



in bootstrap we could do like this:



```php
<?php 
App\Core\App::bind('config', require 'config.php');

App\Core\App::bind('database', new QueryBuilder(
	Connection::make(App::get('config')['database'])
));
```

or better is to import with use command: 

```php
<?php 
use App\Core\App;
App::bind('config', require 'config.php');
....
```

in index.php : 

 ```php
<?php
require 'vendor/autoload.php';
require 'core/bootstrap.php';
use App\Core\Router;
use App\Core\Request;
...
 ```

remember about PDO escapping. 

```php
   return new \PDO($dsn, $username, $password, [
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
            ]);
```



in Router.php :

we have controllers in controller folder so we will  do in call acction : 

```php
public function callAction($controller, $action) 
	{
		$controller = "App\\Controllers\\{$controller}";
		$controller = new $controller;
		if(! method_exists($controller, $action)) {
```



remembe to run 

```
composer-dump autoload 
```





