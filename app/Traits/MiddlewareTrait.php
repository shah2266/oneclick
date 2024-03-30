<?php

namespace App\Traits;

trait MiddlewareTrait {

    public function __construct()
    {
        $this->middleware('check.user.type:1,2')->only('create','edit','destroy','update','store');
    }

}
