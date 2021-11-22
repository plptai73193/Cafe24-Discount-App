<?php
$client_id = (env('APP_ENV') == "production") ? 
	env('CAFE24_APP_CLIENT_ID') : 
	env('CAFE24_APP_CLIENT_ID_DEV');

$client_secret = (env('APP_ENV') == "production") ? 
	env('CAFE24_APP_CLIENT_SECRET') : 
	env('CAFE24_APP_CLIENT_SECRET_DEV');

$redirect_uri = (env('APP_ENV') == "production") ? 
	env('CAFE24_APP_REDIRECT_URI') : 
	env('CAFE24_APP_REDIRECT_URI_DEV');

return [
	/* api */
	'cafe24_api_domain' => env('CAFE24_API_DOMAIN'),
	'cafe24_admin_api_prefix' => env('CAFE24_ADMIN_API_PREFIX'),
	'cafe24_api_version' => env('CAFE24_API_VERSION'),

	/* app */
	"cafe24_app_state" => env('CAFE24_APP_STATE'),
	"cafe24_app_client_id" => $client_id,
	"cafe24_app_client_secret" => $client_secret,
	"cafe24_app_redirect_uri" => $redirect_uri,
	"cafe24_app_scope" => env('CAFE24_APP_SCOPE'),
	"cafe24_app_code" => env('CAFE24_APP_CODE'),
];