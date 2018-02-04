<?php

namespace App\Exceptions;

use App\ResultCode;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
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
                return redirect('/admin?_from=' . urlencode(substr($request->getRequestUri(), 6)));
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
        }else if($exception instanceof ModelNotFoundException){
            $message = '数据不存在: ' . $exception->getModel() . ' -> [ ' . implode(',', $exception->getIds()) . ']';
            $exception = new BaseResponseException($message, ResultCode::DB_QUERY_FAIL);
        }else if($exception instanceof ValidationException){
            $errors = array_map(function(&$value){
                return implode('|', $value);
            }, $exception->errors());
            $exception = new ParamInvalidException(implode('|', $errors));
        }else if($exception instanceof MethodNotAllowedHttpException){
            $exception = new BaseResponseException('不允许的请求方法', ResultCode::UNKNOWN);
        }

        if($exception instanceof BaseResponseException){
            return $exception->getResponse();
        }else {
            // 转换为json
            if(!$request->ajax() && !$request->wantsJson()){
                $request->headers->set('X-Requested-With', 'XMLHttpRequest');
            }
            return parent::render($request, $exception);
        }

    }
}
