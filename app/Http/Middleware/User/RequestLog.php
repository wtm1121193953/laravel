<?php

namespace App\Http\Middleware\User;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
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
        $attributes = $request->attributes->all();
        foreach ($attributes as $key => $attribute) {
            if($attribute instanceof Model){
                $attributes[$key] = $attribute->toArray();
            }
        }
        /** @var Response $response */
        $response = $next($request);
        Log::info('request listen ', [
            'request' => [
                'fullUrl' => $request->fullUrl(),
                'header' => $request->header(),
                'params' => $request->all(),
                'attributes' => $attributes,
                'session' => $request->session()->all(),
            ],
            'response' => [
                'headers' => $response->headers->all(),
                'content' => $response->getContent(),
            ]
        ]);
        return $response;
    }
}
