# 1. PHP

There is really low-level barrier to entry if it comes to PHP

**Table of contents**

1. Arrays  & Associative Array

2. Functions & Lamba(Java:) ) 

3. Using function & superglobal to select current page

4. Code Organization 

5. PDO Connections

   

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


### 5.PDO Connections

http_buid_query to build query string 

