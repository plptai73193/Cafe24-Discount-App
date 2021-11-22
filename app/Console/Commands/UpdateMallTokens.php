<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Facades\Cafe24\Cafe24;
use Illuminate\Support\Facades\DB;
use App\Libs\Cafe24\Cafe24Token;


class UpdateMallTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'malltokens:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $malls = DB::table('malls')->orWhere([
            'is_app_deleted' => 0,      //chưa xóa
            'is_app_expired' => 0,      //chưa hết hạn
        ])->get()->toArray();


        if(!empty($malls)){
            foreach($malls as $mall){

                $app_setting = [
                    "refresh_token" => $mall->refresh_token,
                    "client_id" => (env('APP_ENV') == "production") ? env('CAFE24_APP_CLIENT_ID') : env('CAFE24_APP_CLIENT_ID_DEV'),
                    "client_secret" => (env('APP_ENV') == "production") ? env('CAFE24_APP_CLIENT_SECRET') : env('CAFE24_APP_CLIENT_SECRET_DEV'),
                    "mall_id" => $mall->cafe_mall_id,
                ];
                
                $cafe24NewToken = Cafe24Token::refreshToken($app_setting);

                $cafe_mall_id = $cafe24NewToken->mall_id;
                $access_token = $cafe24NewToken->access_token;
                $refresh_token = $cafe24NewToken->refresh_token;
                
                Cafe24::updateMallToken($cafe_mall_id, $access_token, $refresh_token);
            }
        } else {
            echo 'No mall to refresh token';
        }
    }
}
