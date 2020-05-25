<?php

require __DIR__."/../vendor/autoload.php";


use Core\Loadenv;
use Core\Request;
use Core\Router;

Loadenv::init();

$router = new Router();

$router->get('/users/id:number', 'GraphController@get');
$router->get('/users', 'GraphController@getAll');

$router->run();

// Router::get('/users/#id#', 'GraphController@get');