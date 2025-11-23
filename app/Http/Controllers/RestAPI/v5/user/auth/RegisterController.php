<?php

namespace App\Http\Controllers\RestAPI\v5\user\auth;

use App\Enums\UserRole;
use App\Events\VendorRegistrationEvent;
use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Models\Shop;
use App\Models\User;
use App\Utils\Helpers;
use App\Utils\ImageManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\RestAPI\v5\UserResource;
use App\Http\Resources\RestAPI\v5\UserDataResource;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{

    public function validateData(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'             => 'required|unique:users',
            'first_name'        => 'required',
            'last_name'         => 'required',
            'phone'             => [
                                        'required',
                                        'unique:users',
                                        'regex:/^\d{10,15}$/', // Must be between 10 and 15 digits (handles various country codes)
                                    ],
            'address'           => 'required',
            'password'          => 'required|min:8',
        ]);



        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            $errorMessage = implode(' ', $errors);
            return $this->responseMsg($errorMessage, null, 403);
        }

        // $otp = rand(1000, 9999);
        $otp = 1111;

        $data = [
            'vendorName' => $request['name'],
            'status' => 'pending',
            'subject' => translate('Vendor_Registration_Successfully_Completed'),
            'title' => translate('Vendor_Registration_Successfully_Completed'),
            'userType' => 'vendor',
            'templateName' => 'registration',
        ];

        event(new VendorRegistrationEvent(email: $request['email'], data: $data));
        return $this->responseMsg('OTP has been sent successfully!', ['email'=>$request['email'],'otp' => $otp], 200);
    }

    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'             => 'required|unique:users',
            'first_name'        => 'required',
            'last_name'         => 'required',
            'phone'             => [
                                        'required',
                                        'unique:users',
                                        'regex:/^\d{10,15}$/', // Must be between 10 and 15 digits (handles various country codes)
                                    ],
            'address'           => 'required',
            'password'          => 'required|min:8',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            $errorMessage = implode(' ', $errors);
            return $this->responseMsg($errorMessage, null, 500);
        }

        // DB::beginTransaction();
        try {
            $user = new User();
            $user->email = $request->input('email');
            $user->phone = $request->input('phone');

            $baseUsername = strstr($request->input('email'), '@', true);
            $username = $baseUsername;
            $counter = 1;
            while (User::where('name', $username)->exists()) {
                $username = $baseUsername . '_' . $counter;
                $counter++;
            }
            $user->name = $username;
            $user->f_name = $request->input('first_name');
            $user->l_name = $request->input('last_name');
            $user->street_address = $request->input('address');
            $user->password = Hash::make($request->input('password'));

            $user->save();

            // DB::commit();

            auth()->login($user);
            $tokenResult = $user->createToken('token');
            $user->token = 'Bearer ' . $tokenResult->accessToken;

            // $tokenResult = $user->createToken('auth_token');
            // $token = method_exists($tokenResult, 'plainTextToken') ? $tokenResult->plainTextToken : $tokenResult->accessToken ?? $tokenResult->token;

            return $this->responseMsg(
                'Registration successful',
                    UserDataResource::make($user),
                200
            );
        } catch (\Exception $e) {
            // DB::rollBack();
            return $this->responseMsg('User registration failed!', null, 500);
        }
    }

    public function successResponse($data = null): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'msg' => 'success_fetching_data',
            'status' => 200
        ]);
    }
    public function errorResponse($data = null): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'msg' => 'error fetching data',
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
