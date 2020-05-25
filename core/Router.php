<?php

namespace Core;

use Exception;

class Router {
    
    public $routes = array();

    private $types = [
        'number' => '(\d+)',
        'text' => '([a-zA-Z]+)',
        'mixed' => '(\w+)'
    ];
    
    public function __construct(array $routes = []) {
        $this->register($routes);
    }

    public function get($uri, $action) {
        if($_SERVER['REQUEST_METHOD'] !== 'GET') {
            return;
        }

        return $this->add($uri, $action);
    }

    public function post($uri, $action) {
        if($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        return $this->add($uri, $action);
    }

    private function routeToRegExp($path) {
        $arrPath = explode('/', $path);
        $params = array( 'path' );
        
        
        $arrExp = array();

        foreach ($arrPath as $segment) {
            if (strpos($segment, ':') === false) {
                $arrExp[] = $segment;
            } else {
                $expression = explode(':', $segment);
                $paramName = $expression[0];
                $params[] = $paramName;
                $arrExp[] = isset($this->types[$expression[1]]) ? $this->types[$expression[1]] : $this->types['mixed'];
            }            
        }

        if($arrExp[count($arrExp) -1] == '') {
            unset($arrExp[count($arrExp) -1]);
        }
        
        $regString = '/' . implode('\/', $arrExp).'.*/';

        return array(
            'reg' => $regString,
            'params' => $params
        );
    }

    private function register($routes) {
        foreach ($routes as $path => $fn) {
            $pathReg = $this->routeToRegExp($path);
            $this->routes[$pathReg['reg']] = array(
                'fn' => $fn,
                'params' => $pathReg['params']
            );
        }
    }
    
    private function mapParams($params, $result) {
        $map = array();

        foreach($result as $key => $value) {
            $map[$params[$key]] = $value;
        }

        return $map;
    }
    
    public function run() {
        return $this->route($_SERVER['REQUEST_URI']);
    }
    
    private function route($path) {
        $path = explode('/', substr($path, 1, strlen($path)));
        $selectedFn = null;
        $url = $this->normalizePath($path);
        foreach ($this->routes as $reg => $data) {
            $isMatch = preg_match($reg, $url, $match, PREG_OFFSET_CAPTURE);
            
            if ($isMatch) {
                $selectedFn = $data['fn'];
                break;
            }
        }

        if($selectedFn == null) {
            return false;
        }

        return $this->createCallable($selectedFn, $this->filterParamsData($data['params'], $match));
    }
    
    private function add($path, $fn) {
        $pathReg = $this->routeToRegExp($path);
        $this->routes[$pathReg['reg']] = array(
            'fn' => $fn,
            'params' => $pathReg['params']
        );
    }

    private function normalizePath(array $path): string {
        $url = array();
        foreach($path as $key => $item) {
            if(is_numeric($key)) {
                $url[$key] = $item;
            }
        }

        return '/'.implode('/', $url);
    }

    private function createCallable($action, $params) {
        $request = new Request($params);
        try {
            if(is_callable($action)) {
                return $action($request);
            }
    
            if(gettype($action) === 'string') {
                $parts = explode('@', $action);
                if(count($parts) !== 2) {
                    throw new Exception('Invalid controller or method string');
                }
                $class = '\\App\\Controllers\\'.$parts[0];
                $function = $parts[1];
                $instance = new $class();
                return $instance->$function($request);
            }

            throw new Exception('Type of function passed not recognized');

        } catch (\Throwable $th) {
            Http::error($th->getMessage());
        }
    }

    private function filterParamsData($orderParams, $paramsValues) {
        $params = [];
        foreach($orderParams as $key => $p) {
            $params[$p] =  $paramsValues[$key][0];
        }

        return $params;
    }
}