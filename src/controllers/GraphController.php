<?php

namespace App\Controllers;

use App\Repositories\UserRepository;
use Core\Http;
use Core\Request;

class GraphController {
    public function getAll(Request $request) {
        $rep = new UserRepository();
        Http::success($rep->getAll());
    }

    public function get(Request $request) {
        $rep = new UserRepository();
        Http::success($rep->get($request->url('id')));
    }
}