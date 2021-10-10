<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException as MNFE;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        MNFE::class,
        ValidationException::class,
    ];

    protected array $exceptionMap = [
        ModelNotFoundException::class,
        AuthException::class,
        ValidationException::class,
        InvalidParameterException::class,
    ];
    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        $exceptionClass = get_class($exception);
        if(in_array($exceptionClass, $this->exceptionMap)){
            return parent::render($request, $exception);
        }else {
            // return parent::render($request, $exception);
            return response()->json([
                'error'=>[
                    'status'=>500,
                    'message'=> $exception->getMessage(),
                ]
            ], 500);
        }
    }
}
