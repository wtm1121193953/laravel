<?php

namespace App\Http\Middleware;

use App\ResultCode;
use App\Support\Utils;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\JsonResponse;

class RequestLog
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
        /** @var \Symfony\Component\HttpFoundation\Response|\Illuminate\Contracts\Routing\ResponseFactory $response */
        $response = $next($request);;

        // 如果非生产环境, 记录请求日志
        $logData = [ 'request' => Utils::getRequestContext($request), ];
        if(!App::environment('production')){
            $logData['response'] = [
                'statusCode' => $response->getStatusCode(),
                'content' => json_decode($response->getContent(), 1) ?? $response->getContent(),
            ];
        }
        Log::info('request listen ', $logData);

        // 如果是错误请求, 记录错误日志
        if($response instanceof JsonResponse){
            $responseData = json_decode($response->getContent(), 1);
            if(
                !isset($responseData['code']) ||
                !in_array($responseData['code'], [
                    ResultCode::SUCCESS,
                    ResultCode::PARAMS_INVALID,
                    ResultCode::UNLOGIN,
                    ResultCode::TOKEN_INVALID,
                    ResultCode::USER_ALREADY_BEEN_INVITE,
                    ResultCode::ACCOUNT_NOT_FOUND,
                    ResultCode::ACCOUNT_PASSWORD_ERROR,
                ])
            ){
                $logData = [
                    'request' => Utils::getRequestContext($request),
                    'response' => [
                        'statusCode' => $response->getStatusCode(),
                        'content' => $responseData ?? $response->getContent(),
                    ],
                    'sql_log' => DB::getQueryLog(),
                ];
                Log::error('request listen ', $logData);
            }
        }

        return $response;
    }
}
