<?php

namespace Core;

class Http {

    /**
     * Return http 200
     * 
     * @param array $data Response data
     * 
     * @return json
     */
    public static function success($data) {
        header("HTTP/1.1 200 OK");
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    /**
     * Return http 500
     * 
     * @param array $data Response data
     * 
     * @return json
     */
    public static function error($data) {
        header("HTTP/1.1 500 ERROR");
        header('Content-Type: application/json');
        print_r(json_encode($data));
    }

    /**
     * Return http 404
     * 
     * @param string $data Response data
     * 
     * @return string
     */
    public static function notFound($data = 'Not found') {
        header("HTTP/1.1 404 Not found");
        header('Content-Type: application/json');
        print_r($data);
    }
}