<?php

namespace App\Http\Middleware;

use App\ResultCode;
use App\Support\Utils;
use Closure;
use Illuminate\Database\Eloquent\Model;
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
        if(
            isset($responseData['code']) &&
            in_array($responseData['code'], [
                ResultCode::SUCCESS,
                ResultCode::PARAMS_INVALID,
                ResultCode::UNLOGIN,
                ResultCode::TOKEN_INVALID,
                ResultCode::USER_ALREADY_BEEN_INVITE,
            ])
        ){
            // 如果是成功请求, 不再记录日志
            return $response;
        }

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
                'content' => $responseData ?? 'content is not json',
            ],
            'sql_log' => DB::getQueryLog(),
        ];

        Log::error('request listen ', $logData);
        return $response;
    }
}
