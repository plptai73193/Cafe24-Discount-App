<?php

namespace App\Facades\Cafe24;

use Illuminate\Support\Facades\Facade;

class Cafe24Api extends Facade{
    protected static function getFacadeAccessor() { return 'cafe24api'; }
}