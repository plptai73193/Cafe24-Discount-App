<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Mall;
use \App\Facades\Cafe24\Cafe24;
use \Symfony\Component\HttpFoundation\Response;
use \Illuminate\Support\Facades\Log;

class Cafe24Hook
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            $data = $request->resource;
            
            if (!empty($data)) {            //not hook request
                $cafe_mall_id = $data["mall_id"];

                /****** set global variables ******/
                $cafe24Token = Mall::where(["cafe_mall_id" => $cafe_mall_id])->get()->last();
                if (!empty($cafe24Token)) {
                    Cafe24::setCafe24Token($cafe24Token);
                } else {
                    $message = "No data for {$cafe_mall_id}";
                    Log::info($message);
                    return response()->json([
                        "success" => false,
                        "code" => Response::HTTP_UNAUTHORIZED,
                        "msg" => $message,
                    ]);
                }
            }

            return $next($request);
        } catch(Exception $e) {
            $message = "Invalid request";
            Log::info($message);
            report($e);
            return response()->json([
                "success" => false,
                "code" => Response::HTTP_INTERNAL_SERVER_ERROR,
                "msg" => $message,
            ]);

        }
    }
}
