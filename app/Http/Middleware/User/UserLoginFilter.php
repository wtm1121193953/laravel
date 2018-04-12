<?php

namespace App\Http\Middleware\User;

use App\Exceptions\UnloginException;
use Closure;

class UserLoginFilter
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
        $user = $request->get('current_user');
        if(empty($user)){
            throw new UnloginException();
        }
        return $next($request);
    }
}
