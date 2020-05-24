<?php

namespace App\Repositories;

use Core\Db;
use PDO;

class UserRepository {

    private $instance;

    public function __construct() {
        $this->instance = Db::getInstance();
    }

    public function getAllUser($filters = []) {
            $query = $this->instance->query('SELECT id, firstname, surname, age, gender FROM users');
            $query->execute();
            return $query->fetch(PDO::FETCH_ASSOC);
    }

}