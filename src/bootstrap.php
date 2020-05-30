<?php

require __DIR__."/../vendor/autoload.php";

header("Access-Control-Allow-Origin: *");


use Core\Loadenv;
use Core\Router;

Loadenv::init();

$router = new Router();

$router->get('/users/id:number', 'GraphController@get');
$router->get('/users', 'GraphController@getAll');
$router->get('/graph', 'GraphController@graph');

$router->run();

// Router::get('/users/#id#', 'GraphController@get');