<?php

namespace Core;

class Router {

    public static function get($uri, $action) {
        if($_SERVER['REQUEST_METHOD'] !== 'GET' || $_SERVER['PATH_INFO'] !== trim($uri)) {
            return;
        }

        return self::action($action);
    }


    public static function post($uri, $action) {
        if($_SERVER['REQUEST_METHOD'] !== 'POST' || $_SERVER['PATH_INFO'] !== trim($uri)) {
            return;
        }

        return self::action($action);
    }

    /**
     * Calls action param
     * 
     * @param string|object $action Action wished
     * 
     * @return json
     */
    private static function action($action) {
        if(is_callable($action)) {
            return $action();
        }

        if(gettype($action) === 'string') {
           $parts = explode('@', $action);
           if(count($parts) !== 2) {
               return;
           }
           $class = '\\App\\Controllers\\'.$parts[0];
           $function = $parts[1];
           $instance = new $class();
           return $instance->$function();
        }
    }

}