<?php

namespace App\Http\Middleware;

use App\Support\Utils;
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
        /** @var \Symfony\Component\HttpFoundation\Response|\Illuminate\Contracts\Routing\ResponseFactory $response */
        $response = $next($request);
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
                'content' => 'content is not json'
            ],
        ];
        $data = json_decode($response->getContent(), 1);
        if($data){
            $logData['response']['content'] = $data;
        }

        Log::info('request listen ', $logData);
        return $response;
    }
}
