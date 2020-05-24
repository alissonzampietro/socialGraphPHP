<?php

namespace Core;

use PDO;

class Db {

    private static $instance;

    /**
     * Get PDO instance
     * 
     * @return PDO
     */
    public static function getInstance() {
        self::$instance = new PDO('sqlite:'.__DIR__.'/../storage/'.getenv('SQLITE_FILE'));
        return self::$instance;
    }

    public static function query() {

    }

}