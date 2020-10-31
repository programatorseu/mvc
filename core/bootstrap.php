<?php
$app = [];
$app['config'] = require 'core/config.php';
require 'core/database/Database.php';
require 'core/database/Query.php';
require 'core/Request.php';
require 'core/Router.php';


$app['database'] = new Query(
    Database::connect($config['database'])
);
