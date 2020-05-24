<?php

require __DIR__."/../vendor/autoload.php";


use Core\Loadenv;
use Core\Http;
use Core\Router;

Loadenv::init();

Router::get('/teste/alo', 'GraphController@get');
Router::get('/teste/alo1', 'GraphController@get');
Router::get('/teste/alo2', 'GraphController@get');
Router::get('/teste/alo3', 'GraphController@get');
