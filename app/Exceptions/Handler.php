<?php

namespace App\Exceptions;

use App\Result;
use App\ResultCode;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Auth\Access\AuthorizationException::class,
        \Symfony\Component\HttpKernel\Exception\HttpException::class,
        \Illuminate\Database\Eloquent\ModelNotFoundException::class,
        \Illuminate\Session\TokenMismatchException::class,
        \Illuminate\Validation\ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        if($request->is('api/*')){
            return $this->renderForApi($request, $exception);
        }else if($request->is('boss/*')){
            if($exception instanceof NotFoundHttpException){
                return redirect('/boss?__from=' . urldecode($request->getRequestUri()));
            }
        }
        return parent::render($request, $exception);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Auth\AuthenticationException  $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest(route('login'));
    }

    /**
     * 渲染api的异常
     * @param $request
     * @param Exception $exception
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    private function renderForApi($request, Exception $exception)
    {
        if($exception instanceof NotFoundHttpException){
            return response(['code' => ResultCode::API_NOT_FOUND, 'message' => '接口不存在']);
        }
        if($exception instanceof ModelNotFoundException){
            $message = '数据不存在: ' . $exception->getModel() . ' -> [ ' . implode(',', $exception->getIds()) . ']';
            $exception = new BaseResponseException($message, ResultCode::DB_QUERY_FAIL);
        }
        if($exception instanceof BaseResponseException){
            return $exception->getResponse();
        }else {
            return parent::render($request, $exception);
        }

    }
}
