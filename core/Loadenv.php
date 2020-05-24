<?php

namespace Core;

use Http;

class Loadenv {
    public static function init() {
        $loadedEnvs = false;
        $envs = file(__DIR__.'/../.env');
        foreach($envs as $env) {
            $loadedEnvs = true;
            putenv(trim($env));
        }
        if(!$loadedEnvs) {
            Http::success(['Invalid']);
        }
    }
}