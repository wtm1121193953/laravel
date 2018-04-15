<?php

namespace App\Http\Middleware\User;

use Closure;
use Illuminate\Database\Eloquent\Model;
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
        Log::info('request ' . $request->getSchemeAndHttpHost() . $request->fullUrl(), [
            'header' => $request->header(),
            'params' => $request->all(),
            'attributes' => $attributes,
            'session' => $request->session()->all()
        ]);
        return $next($request);
    }
}
