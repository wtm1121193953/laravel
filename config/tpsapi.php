<?php 
return [
    'register_url'   => env('TPS_APIURL').'/api/daqian/register',
    'check_url'      => env('TPS_APIURL').'/api/daqian/check_user',
	'key'            => env('TPS_APIKEY'),
	'mail_url'       => env('TPS_MAILURL').'/order/sendMail',
	'mail_token'     => env('TPS_MAILTOKEN'),
];