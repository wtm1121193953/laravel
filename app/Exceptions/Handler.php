<?php

namespace App\Exceptions;

use App\ResultCode;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception $exception
     * @return void
     * @throws Exception
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
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Exception $exception)
    {
        if($request->is('api/*')) {
            return $this->renderForApi($request, $exception);
        }else if($request->is('admin/*')){
            if($exception instanceof NotFoundHttpException){
                return redirect('/admin?_from=' . urlencode($request->fullUrl()));
            }
        }
        return parent::render($request, $exception);
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
            return response(['code' => ResultCode::API_NOT_FOUND, 'message' => '接口不存在', 'timestamp' => time()]);
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
