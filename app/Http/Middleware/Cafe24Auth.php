<?php

namespace App\Http\Middleware;

use Closure;
use \Symfony\Component\HttpFoundation\Response;
use \Illuminate\Support\Facades\Log;
use \App\Facades\Cafe24\Cafe24;
use \App\Models\Mall;
use \App\Models\Logs\MallAccessLog;
use \App\Services\MallService;

class Cafe24Auth
{
    protected $mallService;

    public function __construct(MallService $mallService){
        $this->mallService = $mallService;
    }

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
            $cafe_mall_id = $request->cafe_mall_id;
            $access_token = @$request->access_token;
			$refresh_token = @$request->refresh_token;
			$not_get_mall = @$request->not_get_mall;

            $key_options_params = [
                "client_id",
                "client_secret",
            ];
            

            /****** set get mall params ******/
            $getMallParams = [
                "cafe_mall_id" => $cafe_mall_id,
                "refresh_token" => $refresh_token,
            ];

            foreach ($key_options_params as $_key) {
                if (!empty($request->{$_key})) {
                    $getMallParams[$_key] = $request->{$_key};
                }
            }


            /****** get mall is existed cafe24 mall id ******/
            
            //get mall info
            if (!empty($cafe_mall_id)) {
                $get_mall = $this->mallService->getMallAndCreate($getMallParams, $access_token);
                
                if ($get_mall["success"] === true) {          //get mall success
					$mall = $get_mall["data"]["mall"];
					$mall_id = $mall["id"];

                    $cafe24Token = [
                        "mall_id" => $mall_id,
                        "cafe_mall_id" => $mall["cafe_mall_id"],
                        "access_token" => $mall["access_token"],
                        "refresh_token" => $mall["refresh_token"],
                    ];
                    Cafe24::setCafe24Token($cafe24Token);

                    $getMallParams["mall_id"] = $mall_id;
                    
					/*********** Save Access Log ***********/
					if (!empty($mall)) {
                        $mallaccesslog = [
                            "mall_id" => $mall_id,
                            "cafe_mall_id" => $cafe_mall_id,
                            "created_at" => time(),
                        ];

                        MallAccessLog::insert($mallaccesslog);
                    } else {                                                //get target mall failed
                        $message = "Log Target Mall: " . $mall["errors"];
                        Log::error($message);
                        return response()->json([
                            "success" => false,
                            "code" => Response::HTTP_UNAUTHORIZED,
                            "msg" => $message,
                        ]);
                    }
                } else {            //get mall failed
                    $message = !empty($get_mall["errors"]) ? $get_mall["errors"] : $get_mall["msg"];
                    Log::error($message);
                    return response()->json([
                        "success" => false,
                        "code" => Response::HTTP_UNAUTHORIZED,
                        "msg" => $message,
                    ]);
                }
            } else {            //set setCafe24Token by request payload
                $cafe_mall_id = !empty($request->cafe_mall_id) ? $request->cafe_mall_id : $request->market_mall_id;
                $mall_id = @$request->mall_id;
                $cafe24Token = [
                    "mall_id" => $mall_id,
                    "cafe_mall_id" => $cafe_mall_id,
                    "access_token" => $access_token,
                    "refresh_token" => $refresh_token,
                ];
                Cafe24::setCafe24Token($cafe24Token);
            }            

            return $next($request)->header('Access-Control-Allow-Origin', '*');
        } catch(Exception $e) {
            $message = "Invalid request";
            Log::error($message);
            report($e);
            return response()->json([
                "success" => false,
                "code" => Response::HTTP_INTERNAL_SERVER_ERROR,
                "msg" => $message,
            ]);

        }
    }
}
