<?php

namespace App\Http\Controllers\RestAPI\v5\user\auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\RestAPI\v5\UserResource;
use App\Http\Resources\RestAPI\v5\UserDataResource;
use App\Models\Seller;
use App\Models\SellerWallet;
use App\Models\User;
use App\Utils\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginController extends Controller
{
    // public function login(Request $request): JsonResponse
    // {
    //     $validator = Validator::make($request->all(), [
    //         'identity' => 'required',
    //         'password' => 'required|min:6'
    //     ]);

    //     if ($validator->fails()) {
    //         $errors = $validator->errors()->all();
    //         $errorMessage = implode(' ', $errors);
    //         return $this->responseMsg($errorMessage, null, 403);
    //     }

    //     $user = User::where('name', $request->identity)
    //         ->orWhere('phone', $request->identity)
    //         ->first();

    //     if (!$user || !Hash::check($request->password, $user->password)) {
    //         return $this->responseMsg('Invalid credentials', null, 401);
    //     }

    //         auth()->login($user);
    //         $tokenResult = $user->createToken('token');
    //         $user->token = 'Bearer ' . $tokenResult->accessToken;

    //     return $this->responseMsg("you have logged in successfully",UserDataResource::make($user), 200);
    // }


    public function login(Request $request): JsonResponse
    {
        $credentials = $request->only('identity', 'password');
        $user = User::where('name', $request->identity)
            ->orWhere('phone', $request->identity)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return $this->responseMsg('Invalid credentials', null, 401);
        }

        $token = JWTAuth::fromUser($user);

        $user->token = 'Bearer ' . $token;

        return $this->responseMsg("you have logged in successfully", UserDataResource::make($user), 200);
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            // Invalidate the token
            JWTAuth::invalidate(JWTAuth::getToken());

            return $this->responseMsg('Successfully logged out', null, 200);
        } catch (\Exception $e) {
            return $this->responseMsg('Failed to logout, please try again', null, 500);
        }
    }

    public function successResponse($data = null): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'msg' => 'success fetching data',
            'status' => 200
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function errorResponse($data = null): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'msg' => 'error fetching data',
            'status' => 500
        ]);
    }

    public function responseMsg($msg, $data = null, int $status = 200): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'data' => $data,
            'msg' => $msg,
            'status' => $status
        ]);
    }
}
