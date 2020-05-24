<?php

require __DIR__."/../vendor/autoload.php";


use Core\Loadenv;
use Core\Router;

Loadenv::init();

Router::get('/users/#id#', 'GraphController@get');