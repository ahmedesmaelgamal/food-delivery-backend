<?php
//
//namespace App\Exceptions;
//
//use App\Traits\ErrorLogsTrait;
//use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
//use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
//use Tymon\JWTAuth\Exceptions\TokenInvalidException;
//use Tymon\JWTAuth\Exceptions\TokenExpiredException;
//use Illuminate\Http\JsonResponse;
//
//use Throwable;
//
//class Handler extends ExceptionHandler
//{
//    use ErrorLogsTrait;
//
//    /**
//     * A list of the exception types that are not reported.
//     *
//     * @var array
//     */
//    protected $dontReport = [
//        //
//    ];
//
//    /**
//     * A list of the inputs that are never flashed for validation exceptions.
//     *
//     * @var array
//     */
//    protected $dontFlash = [
//        'password',
//        'password_confirmation',
//    ];
//
//    /**
//     * Report or log an exception.
//     *
//     * @param \Throwable $exception
//     * @return void
//     *
//     * @throws \Throwable
//     */
//    public function report(Throwable $exception)
//    {
//        parent::report($exception);
//    }
//
//    /**
//     * Render an exception into an HTTP response.
//     *
//     * @param \Illuminate\Http\Request $request
//     * @param \Throwable $exception
//     * @return \Symfony\Component\HttpFoundation\Response
//     *
//     * @throws \Throwable
//     */
//    // public function render($request, Throwable $exception)
//    // {
//    //     if ($this->isHttpException($exception) && $exception?->getStatusCode() == 404) {
//    //         $redirectUrl = $this->storeErrorLogsUrl(url: $request->fullUrl(), statusCode: $exception->getStatusCode());
//    //         if ($redirectUrl && isset($redirectUrl['redirect_url'])) {
//    //             return redirect(to: $redirectUrl['redirect_url'], status: ($redirectUrl['redirect_status'] ?? '301'));
//    //         }
//    //     }
//    //     return parent::render($request, $exception);
//    // }
//
//
//    public function render($request, Throwable $exception)
//{
//    if ($exception instanceof UnauthorizedHttpException) {
//        return $this->responseMsg(
//            'Authentication token is missing',
//            null,
//            status: 422);
//    }
//
//    // Handle other JWT exceptions if needed
//    if ($exception instanceof TokenInvalidException) {
//        return $this->responseMsg('Token is invalid', null, 422);
//    }
//
//    if ($exception instanceof TokenExpiredException) {
//        return $this->responseMsg('Token has expired', null, 422);
//    }
//
//    return parent::render($request, $exception);
//
//}
//
//
//        public function responseMsg($msg, $data = null, int $status = 200): JsonResponse
//    {
//        return response()->json([
//            'data' => $data,
//            'msg' => $msg,
//            'status' => $status
//        ]);
//    }
//}






namespace App\Exceptions;

use App\Traits\ErrorLogsTrait;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Illuminate\Http\JsonResponse;
use Throwable;

class Handler extends ExceptionHandler
{
    use ErrorLogsTrait;

    protected $dontReport = [
        //
    ];

    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

//    public function render($request, Throwable $exception)
//    {
//        // These exceptions might still occur in other routes that use strict JWT auth
////        if ($exception instanceof UnauthorizedHttpException) {
////            return $this->responseMsg(
////                'Authentication token is missing',
////                null,
////                status: 422
////            );
////        }
//
//        if ($exception instanceof TokenInvalidException) {
//            return $this->responseMsg('Token is invalid', null, 422);
//        }
//
//        if ($exception instanceof TokenExpiredException) {
//            return $this->responseMsg('Token has expired', null, 422);
//        }
//
//        return parent::render($request, $exception);
//    }





//    public function render($request, Throwable $exception)
//    {
//        // Handle JWT-specific exceptions
//        if ($exception instanceof UnauthorizedHttpException) {
//            // Check the previous exception to detect real cause
//            $previous = $exception->getPrevious();
//
//            if ($previous instanceof TokenExpiredException) {
//                return $this->responseMsg('Token has expired', null, 422);
//            }
//
//            if ($previous instanceof TokenInvalidException) {
//                return $this->responseMsg('Token is invalid', null, 422);
//            }
//
//            if ($previous instanceof JWTException) {
//                return $this->responseMsg('Token Signature could not be verified', null, 422);
//            }
//
//            return $this->responseMsg('Unauthorized: Invalid or missing token', null, 422);
//        }
//
//        if ($exception instanceof TokenInvalidException) {
//            return $this->responseMsg('Token is invalid', null, 422);
//        }
//
//        if ($exception instanceof TokenExpiredException) {
//            return $this->responseMsg('Token has expired', null, 422);
//        }
//
//        return parent::render($request, $exception);
//    }




//    public function render($request, Throwable $exception)
//    {
//        // Remove all JWT exception handling since we use Passport
//
//        if ($this->isHttpException($exception) && $exception?->getStatusCode() == 404) {
//            $redirectUrl = $this->storeErrorLogsUrl(url: $request->fullUrl(), statusCode: $exception->getStatusCode());
//            if ($redirectUrl && isset($redirectUrl['redirect_url'])) {
//                return redirect(to: $redirectUrl['redirect_url'], status: ($redirectUrl['redirect_status'] ?? '301'));
//            }
//        }
//
//        return parent::render($request, $exception);
//    }




//    public function render($request, Throwable $exception)
//    {
////        dd($exception);
//        // Handle authent
//        if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
//            // Log debug information
//            \Log::info('Auth Debug Info:', [
//                'has_authorization_header' => $request->hasHeader('Authorization'),
//                'authorization_header' => $request->header('Authorization'),
//                'bearer_token' => $request->bearerToken(),
//                'all_headers' => $request->headers->all(),
//                'guard' => $exception->guards(),
//                'url' => $request->fullUrl(),
//            ]);
//
////            return response()->json([
////                'message' => 'Unauthenticated.',
////                'status' => 401,
////                'debug' => config('app.debug') ? [
////                    'has_auth_header' => $request->hasHeader('Authorization'),
////                    'bearer_token_present' => !empty($request->bearerToken()),
////                ] : null
////            ], 401);
//            $this->responseMsg('Unauthenticated', null, 401);
//        }
//
//        if ($this->isHttpException($exception) && $exception?->getStatusCode() == 404) {
//            $redirectUrl = $this->storeErrorLogsUrl(url: $request->fullUrl(), statusCode: $exception->getStatusCode());
//            if ($redirectUrl && isset($redirectUrl['redirect_url'])) {
//                return redirect(to: $redirectUrl['redirect_url'], status: ($redirectUrl['redirect_status'] ?? '301'));
//            }
//        }
//
//        //ication exceptions for API requests
//        if ($request->expectsJson() || $request->is('api/*') || $request->is('v5/*')) {
//
//            // Token expired
//            if ($exception instanceof TokenExpiredException) {
////                return response()->json([
////                    'msg' => 'Token has expired',
////                    'data'=>null,
////                    'status' => 401
////                ], 401);
//                $this->responseMsg('Token has expired', null, 401);
//
//
//            }
//
//            // Token invalid
//            if ($exception instanceof TokenInvalidException) {
////                return response()->json([
////                    'msg' => 'Token is invalid',
////                    'data'=>null,
////                    'status' => 401
////                ], 401);
//                $this->responseMsg('Token is invalid', null, 401);
//            }
//
//            // Unauthenticated (Passport/Sanctum)
//            if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
////                return response()->json([
////                    'msg' => 'Unauthenticated.',
////                    'status' => 401
////                ], 401);
//                $this->responseMsg('Unauthenticated', null, 401);
//
//            }
//
//            // Unauthorized
//            if ($exception instanceof UnauthorizedHttpException) {
////                return response()->json([
////                    'message' => 'Unauthorized access',
////                    'status' => 401
////                ], 401);
//                $this->responseMsg('Unauthorized access', null, 401);
//
//            }
//        }
//
//        if ($this->isHttpException($exception) && $exception?->getStatusCode() == 404) {
//            $redirectUrl = $this->storeErrorLogsUrl(url: $request->fullUrl(), statusCode: $exception->getStatusCode());
//            if ($redirectUrl && isset($redirectUrl['redirect_url'])) {
//                return redirect(to: $redirectUrl['redirect_url'], status: ($redirectUrl['redirect_status'] ?? '301'));
//            }
//        }
//
//        return parent::render($request, $exception);
//    }




    public function render($request, Throwable $exception)
    {
        // Handle Authentication exceptions
        if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
            \Log::info('Auth Debug Info:', [
                'has_authorization_header' => $request->hasHeader('Authorization'),
                'authorization_header' => $request->header('Authorization'),
                'bearer_token' => $request->bearerToken(),
                'all_headers' => $request->headers->all(),
                'guard' => $exception->guards(),
                'url' => $request->fullUrl(),
            ]);

            return $this->responseMsg('Unauthenticated', null, 401);
        }

        // Handle API-related exceptions
        if ($request->expectsJson() || $request->is('api/*') || $request->is('v5/*')) {

            if ($exception instanceof TokenExpiredException) {
                return $this->responseMsg('Token has expired', null, 401);
            }

            if ($exception instanceof TokenInvalidException) {
                return $this->responseMsg('Token is invalid', null, 401);
            }

            if ($exception instanceof UnauthorizedHttpException) {
                return $this->responseMsg('Unauthorized access', null, 401);
            }
        }

        // Handle 404 errors (optional)
        if ($this->isHttpException($exception) && $exception?->getStatusCode() == 404) {
            $redirectUrl = $this->storeErrorLogsUrl(url: $request->fullUrl(), statusCode: $exception->getStatusCode());
            if ($redirectUrl && isset($redirectUrl['redirect_url'])) {
                return redirect(to: $redirectUrl['redirect_url'], status: ($redirectUrl['redirect_status'] ?? '301'));
            }
        }

        return parent::render($request, $exception);
    }

    public function responseMsg($msg, $data = null, int $status = 200): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'msg' => $msg,
            'status' => $status
        ]);
    }
}
