<?php

namespace App\Http\Middleware;

use App\ResultCode;
use App\Support\Utils;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        $response = $next($request);
        $responseData = json_decode($response->getContent(), 1);

        // 如果非生产环境, 记录请求日志
        if(!App::environment('production')){
            $attributes = $request->attributes->all();
            foreach ($attributes as $key => $attribute) {
                if($attribute instanceof Model){
                    $attributes[$key] = $attribute->toArray();
                }
            }
            $logData = [
                'request' => Utils::getRequestContext($request),
                'response' => [
                    'statusCode' => $response->getStatusCode(),
                    'content' => $responseData ?? $response->getContent(),
                ],
            ];
            Log::info('request listen ', $logData);
        }

        // 如果是错误请求, 记录错误日志
        if(
            !(
                $request->is('api/oper/inviteChannel/downloadInviteQrcode')
                || $request->is('api/merchant/inviteChannel/downloadInviteQrcode')
                || $request->is('api/pay/notify')
                || $request->is('/pay/reapalPayNotify')
            )
            && !(
                isset($responseData['code']) &&
                in_array($responseData['code'], [
                    ResultCode::SUCCESS,
                    ResultCode::PARAMS_INVALID,
                    ResultCode::UNLOGIN,
                    ResultCode::TOKEN_INVALID,
                    ResultCode::USER_ALREADY_BEEN_INVITE,
                    ResultCode::ACCOUNT_NOT_FOUND,
                    ResultCode::ACCOUNT_PASSWORD_ERROR,
                ])
            )
        ){
            $attributes = $request->attributes->all();
            foreach ($attributes as $key => $attribute) {
                if($attribute instanceof Model){
                    $attributes[$key] = $attribute->toArray();
                }
            }
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

        return $response;
    }
}
