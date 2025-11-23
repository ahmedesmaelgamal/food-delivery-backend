<?php
////
//namespace App\Http\Middleware;
////
//use Closure;
//use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Auth;
//use Tymon\JWTAuth\Facades\JWTAuth;
//use Tymon\JWTAuth\Exceptions\TokenExpiredException;
//use Tymon\JWTAuth\Exceptions\TokenInvalidException;
//use Tymon\JWTAuth\Exceptions\JWTException;
//use Symfony\Component\HttpFoundation\Response;
//use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
//
////
////class OptionalAuthMiddleware extends BaseMiddleware
////{
////    public function handle(Request $request, Closure $next)
////    {
////        try {
////            if ($request->bearerToken()) {
////                $user = JWTAuth::parseToken()->authenticate();
////
////                if ($user) {
////                    Auth::setUser($user);
////                }
////            }
////        } catch (TokenInvalidException $e) {
////            throw new TokenInvalidException('Token is Invalid');
////        } catch (TokenExpiredException $e) {
////            throw new TokenExpiredException('Token is Expired');
////        } catch (JWTException $e) {
////            throw new JWTException('Authorization Token not found');
////        }
////
////        return $next($request);
////    }
////}
//
//
////namespace App\Http\Middleware;
//
////use Closure;
////use Illuminate\Http\Request;
////use Illuminate\Support\Facades\Auth;
////use Tymon\JWTAuth\Facades\JWTAuth;
//
//
//class OptionalAuthMiddleware
//{
//    /**
//     * Handle an incoming request.
//     *
//     * @param \Illuminate\Http\Request $request
//     * @param \Closure $next
//     * @return mixed
//     */
//    public function handle(Request $request, Closure $next)
//    {
//        // Check if authorization header exists and is a Bearer token
//        if ($request->hasHeader('Authorization') &&
//            str_starts_with($request->header('Authorization'), 'Bearer ')) {
//
//            $token = substr($request->header('Authorization'), 7);
//
//            try {
//                // Try to set the token and authenticate
//                JWTAuth::setToken($token);
//
//                if ($user = JWTAuth::authenticate()) {
//                    Auth::setUser($user);
//                }
//            } catch (TokenInvalidException $e) {
//                // Token is invalid - just continue without authentication
//                // DON'T throw the exception!
//            } catch (TokenExpiredException $e) {
//                // Token is expired - just continue without authentication
//                // DON'T throw the exception!
//            } catch (JWTException $e) {
//                // Other JWT errors - just continue without authentication
//                // DON'T throw the exception!
//            } catch (\Exception $e) {
//                // Any other exceptions - continue without authentication
//                // DON'T throw the exception!
//            }
//        }
//
//        return $next($request);
//    }
//}
//



namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Exceptions\JWTException;

class OptionalAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->hasHeader('Authorization') &&
            str_starts_with($request->header('Authorization'), 'Bearer ')) {

            try {
                $token = substr($request->header('Authorization'), 7);
                JWTAuth::setToken($token);

                if ($user = JWTAuth::authenticate()) {
                    Auth::setUser($user);
                }

            } catch (TokenInvalidException $e) {
                // Invalid token — continue without user
            } catch (TokenExpiredException $e) {
                // Expired token — continue without user
            } catch (JWTException $e) {
                // Missing or malformed token — continue
            } catch (\Exception $e) {
                // Any other error — continue
            }
        }

        return $next($request);
    }
}