<?php

namespace App\Exceptions;

use App\Result;
use App\ResultCode;
use App\Support\Utils;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        }else if($request->is('oper/*')){
            if($exception instanceof NotFoundHttpException){
                return redirect('/oper?_from=' . urlencode(substr($request->getRequestUri(), 5)));
            }
        }else if($request->is('merchant/*')){
            if($exception instanceof NotFoundHttpException){
                return redirect('/merchant?_from=' . urlencode(substr($request->getRequestUri(), 9)));
            }
        }else if($request->is('merchant-h5/*')){
            if($exception instanceof NotFoundHttpException){
                return redirect('/merchant-h5');
            }
        }/*else if($request->is('user-h5/*')){
            if($exception instanceof NotFoundHttpException){
                return redirect('/user-h5?_from=' . urlencode(substr($request->getRequestUri(), 8)));
            }
        }*/
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
            $response = Result::error(ResultCode::DB_QUERY_FAIL, $message);
        }else if($exception instanceof ValidationException){
            $errors = array_map(function(&$value){
                return implode('|', $value);
            }, $exception->errors());
            $response = Result::error(ResultCode::PARAMS_INVALID, implode('|', $errors));
        }else if($exception instanceof MethodNotAllowedHttpException){
            $response = Result::error(ResultCode::UNKNOWN, '不允许的请求方法');
        }else if($exception instanceof BaseResponseException){
            $response = $exception->getResponse();
        }else {
            // 转换为json
            if(!$request->ajax() && !$request->wantsJson()){
                $request->headers->set('X-Requested-With', 'XMLHttpRequest');
            }
            $response = parent::render($request, $exception);
        }
        // 如果
        if(DB::transactionLevel() > 0){
            DB::rollBack(0);
        }
        $result = json_decode($response->getContent(), 1);
        if(
            !isset($result['code']) ||
            !in_array($result['code'], [
                ResultCode::PARAMS_INVALID,
                ResultCode::UNLOGIN,
                ResultCode::TOKEN_INVALID,
                ResultCode::USER_ALREADY_BEEN_INVITE,
            ])
        ){
            Log::error('exception handler listen', [
                'request' => Utils::getRequestContext($request),
                'response' => [
                    'statusCode' => $response->getStatusCode(),
                    'headers' => $response->headers->all(),
                    'content' => json_decode($response->getContent(), 1),
                ]
            ]);
        }

        return $response;
    }
}
