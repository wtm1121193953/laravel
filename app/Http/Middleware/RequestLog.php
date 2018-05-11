<?php

namespace App\Http\Middleware;

use App\Support\Utils;
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
        $response = $next($request);
        $attributes = $request->attributes->all();
        foreach ($attributes as $key => $attribute) {
            if($attribute instanceof Model){
                $attributes[$key] = $attribute->toArray();
            }
        }
        Log::info('request listen ', [
            'request' => Utils::getRequestContext($request),
            'response' => $response,
        ]);
        return $response;
    }
}
