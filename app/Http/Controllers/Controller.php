<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function getPerPage(\Illuminate\Http\Request $request, int $default = 15): int
    {
        return min(max($request->integer('per_page', $default), 1), 100);
    }
}
