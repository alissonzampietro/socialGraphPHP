<?php

namespace Core;

class Request {

    private $urlParams;

    public function __construct(array $params) {
        $this->urlParams = $params;
    }

    /**
     * Return param sent to request according name defined in the route in boostrap
     * 
     * @param string $name Name of field
     * 
     * @return string|null
     */
    public function url(string $name) {
        if(empty($this->urlParams[$name])) {
            return null;
        }

        return $this->urlParams[$name];
    }
}