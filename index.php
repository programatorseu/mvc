<?php
$query = require 'core/bootstrap.php';
$posts = $query->selectAll('posts');
var_dump($posts);

