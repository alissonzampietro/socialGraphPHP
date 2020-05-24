<?php

namespace App\Controllers;

use App\Repositories\UserRepository;
use Core\Http;
use Core\Request;

class GraphController {
    public function get(Request $req) {
        $rep = new UserRepository();
        Http::success($rep->getAllUser());
    }
}