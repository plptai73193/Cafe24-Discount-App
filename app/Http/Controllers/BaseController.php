<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BaseController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getError(Exception $e) {
        report($e);
        return response()->json([
            "success" => false,
            "code" => Response::HTTP_INTERNAL_SERVER_ERROR,
            "msg" => $e->getMessage(),
        ]);
    }
}
