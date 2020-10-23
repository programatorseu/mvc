<?php
$config = require 'core/config.php';
require 'core/database/Database.php';
require 'core/database/Query.php';

return new Query(
    Database::connect($config['database'])
);
