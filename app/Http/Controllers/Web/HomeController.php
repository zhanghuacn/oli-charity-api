<?php

namespace App\Http\Controllers\Web;

class HomeController
{
    public function index()
    {
        return phpinfo();
    }
}
