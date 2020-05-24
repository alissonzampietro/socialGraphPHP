<?php

namespace App\Database;

use Exception;

class Mysql implements Database {

    private static $instance = null;

    public function __construct($env = "DEV")
    {
        switch($env) {
            case "DEV":
        }
        
    }

    public function __clone() {
        throw new Exception("Can't clone a database");
    }
}