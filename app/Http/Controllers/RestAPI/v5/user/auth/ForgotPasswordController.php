<?php

namespace App\Http\Controllers\RestAPI\v5\user\auth;

use App\Events\PasswordResetEvent;
use App\Http\Controllers\Controller;
use App\Mail\SendOtpMail;
use App\Models\User;
use App\Utils\Helpers;
use App\Utils\SMSModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Modules\Gateways\Traits\SmsGateway;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\RestAPI\v5\UserResource;
use App\Http\Resources\RestAPI\v5\UserDataResource;
use Tymon\JWTAuth\Facades\JWTAuth;

class ForgotPasswordController extends Controller
{
    public function reset_password_request(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identity' => 'required|min:6', // This can be email or phone number
        ]);

        if ($validator->fails()) {
            return $this->responseMsg(  Helpers::validationErrorProcessor($validator), null,403);
        }

        $identity = $request->input('identity');
        if (is_numeric($identity)) {
            $user = User::where('phone', 'like', "%{$identity}%")->first();
        } else {
            $user = User::where('name', $identity)->first();
        }
        if (!$user) {
            return $this->responseMsg( 'User not found!',null, 404);
        }

        // $otp = rand(100000, 999999);
        $otp = 1111;

        return $this->responseMsg('OTP has been sent successfully!', ['identity'=>$request['identity'],'otp' => $otp], 200);
    }


    public function reset_password_submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identity' => 'required|string',
            'new_password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return $this->responseMsg( Helpers::validationErrorProcessor($validator),null, 403);
        }

        try {
            $identity = $request->input('identity');
            $newPassword = $request->input('new_password');


            if (is_numeric($identity)) {
                $user = User::where('phone', 'like', "%{$identity}%")->first();
            } else {
                $user = User::where('name', $identity)->first();
            }

            if (!$user) {
                return $this->responseMsg( 'User not found!',null, 404);
            }

            $user->password = bcrypt(str_replace(' ', '', $newPassword));
            $user->save();

            $token = JWTAuth::fromUser($user);

            $user->token = 'Bearer ' . $token;
            return $this->responseMsg(
                'the password has been reset successfully',
                [
                    'user' => UserDataResource::make($user),
                ],
                200
            );
        } catch (\Exception $e) {
            return $this->responseMsg('reset password has been failed!', null, 500);
        }
    }

    public function successResponse($data = null): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'msg' => translate('success_fetching_data'),
            'status' => 200
        ]);
    }
    public function errorResponse($data = null): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'msg' => translate('error_fetching_data'),
            'status' => 500
        ]);
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
