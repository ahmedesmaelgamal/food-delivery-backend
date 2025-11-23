<?php

namespace App\Http\Controllers\RestAPI\v5\user\auth;

use Illuminate\Support\Facades\Http;  // ADD THIS LINE
use App\Events\PasswordResetEvent;
use App\Http\Controllers\Controller;
use App\Mail\SendOtpMail;
use App\Models\User;
use App\Events\VendorRegistrationEvent;
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
use App\Models\DeviceToken;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;


class AuthController extends Controller
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
//            'address'           => 'required',
            'password'          => 'required|min:8',
        ]);



        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            $errorMessage = implode(' ', $errors);
            return $this->responseMsg($errorMessage, null, 403);
        }


            $identity = $request->input('email');
            // $otp = rand(100000, 999999);
            $otp = 1111;

            // Send OTP via email if identity is email
            if (filter_var($identity, FILTER_VALIDATE_EMAIL)) {
//                try {
//                    Mail::to($identity)->send(new SendOtpMail([
//                        'email' => $identity,
//                        'otp' => $otp
//                    ]));

//                } catch (\Exception $e) {
//                    return $this->responseMsg('Failed to send OTP email.', null, 500);
//                }
            }
            

        // $otp = rand(1000, 9999);
//        $otp = 1111;

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

//    public function register(Request $request): JsonResponse
//    {
//        $validator = Validator::make($request->all(), [
//            'email'             => 'required|unique:users',
//            'first_name'        => 'required',
//            'last_name'         => 'required',
//            'phone'             => [
//                                        'required',
//                                        'unique:users',
//                                        'regex:/^\d{10,15}$/', // Must be between 10 and 15 digits (handles various country codes)
//                                    ],
////            'address'           => 'required',
//            'password'          => 'required|min:8',
//        ]);
//
//        if ($validator->fails()) {
//            $errors = $validator->errors()->all();
//            $errorMessage = implode(' ', $errors);
//            return $this->responseMsg($errorMessage, null, 500);
//        }
//
//        // DB::beginTransaction();
//        try {
//            $user = new User();
//            $user->email = $request->input('email');
//            $user->phone = $request->input('phone');
//
//            $baseUsername = strstr($request->input('email'), '@', true);
//            $username = $baseUsername;
//            $counter = 1;
//            while (User::where('name', $username)->exists()) {
//                $username = $baseUsername . '_' . $counter;
//                $counter++;
//            }
//            $user->name = $username;
//            $user->f_name = $request->input('first_name');
//            $user->l_name = $request->input('last_name');
//            $user->street_address = $request->input('address');
//            $user->password = Hash::make($request->input('password'));
//
//            $user->save();
//
//            // DB::commit();
//
//
//
//            auth()->login($user);
//            $tokenResult = $user->createToken('token');
//            $user->token = 'Bearer ' . $tokenResult->accessToken;
//
//            // $tokenResult = $user->createToken('auth_token');
//            // $token = method_exists($tokenResult, 'plainTextToken') ? $tokenResult->plainTextToken : $tokenResult->accessToken ?? $tokenResult->token;
//
//            return $this->responseMsg(
//                'Registration successful',
//                    UserDataResource::make($user),
//                200
//            );
//        } catch (\Exception $e) {
//            // DB::rollBack();
//            return $this->responseMsg('User registration failed!', null, 500);
//        }
//    }



//    public function register(Request $request): JsonResponse
//    {
//        $validator = Validator::make($request->all(), [
//            'email'             => 'required|unique:users',
//            'first_name'        => 'required',
//            'last_name'         => 'required',
//            'phone'             => [
//                'required',
//                'unique:users',
//                'regex:/^\d{10,15}$/',
//            ],
//            'password'          => 'required|min:8',
//        ]);
//
//        if ($validator->fails()) {
//            $errors = $validator->errors()->all();
//            $errorMessage = implode(' ', $errors);
//            return $this->responseMsg($errorMessage, null, 500);
//        }
//
//        DB::beginTransaction();
////        try {
//            // Create user in local database
//            $user = new User();
//            $user->email = $request->input('email');
//            $user->phone = $request->input('phone');
//
//            $baseUsername = strstr($request->input('email'), '@', true);
//            $username = $baseUsername;
//            $counter = 1;
//            while (User::where('name', $username)->exists()) {
//                $username = $baseUsername . '_' . $counter;
//                $counter++;
//            }
//            $user->name = $username;
//            $user->f_name = $request->input('first_name');
//            $user->l_name = $request->input('last_name');
//            $user->street_address = $request->input('address');
//            $user->password = Hash::make($request->input('password'));
//            $user->wp_password = Crypt::encryptString($request->password); // 🔒 safely store for WP ops
//
//
//        $user->save();
//
//            // Create user in WordPress via API
//        $displayName = $request->input('first_name') . ' ' . $request->input('last_name');
//
//        $wpResponse = Http::asForm()->post(
//            'https://maximfood.com/?rest_route=/simple-jwt-login/v1/users',
//            [
//                'email' => $request->input('email'),
//                'password' => $request->input('password'),
//                'display_name' => $displayName,
//                'user_url' => $request->input('phone'),
//            ]
//        );
//
//// Debug the response
//        \Log::info('WordPress Response:', [
//            'status' => $wpResponse->status(),
//            'body' => $wpResponse->body(),
//            'json' => $wpResponse->json(),
//        ]);
//
//        if (!$wpResponse->successful()) {
//            throw new \Exception('WordPress user creation failed: ' . $wpResponse->body());
//        }
//
//        $wpData = $wpResponse->json();
//
//            // Optionally store WordPress user ID and JWT token
////            if (isset($wpData['id'])) {
////                $user->wp_user_id = $wpData['id']; // Add this column to your users table
////                $user->wp_jwt_token = $wpData['jwt'] ?? null; // Optional: store JWT
////                $user->save();
////            }
//
//            DB::commit();
//
//            auth()->login($user);
//            $tokenResult = $user->createToken('token');
//            $user->token = 'Bearer ' . $tokenResult->accessToken;
//
//            return $this->responseMsg(
//                'Registration successful',
//                UserDataResource::make($user),
//                200
//            );
////        } catch (\Exception $e) {
////            DB::rollBack();
////            \Log::error('User registration failed: ' . $e->getMessage());
////            return $this->responseMsg('User registration failed: ' . $e->getMessage(), null, 500);
////        }
//    }




//    public function register(Request $request): JsonResponse
//    {
//        $validator = Validator::make($request->all(), [
//            'email'      => 'required|unique:users',
//            'first_name' => 'required',
//            'last_name'  => 'required',
//            'phone'      => ['required', 'unique:users', 'regex:/^\d{10,15}$/'],
//            'password'   => 'required|min:8',
//        ]);
//
//        if ($validator->fails()) {
//            $errors = $validator->errors()->all();
//            $errorMessage = implode(' ', $errors);
//            return $this->responseMsg($errorMessage, null, 500);
//        }
//
//        DB::beginTransaction();
//        try {
//            // Create user locally
//            $user = new User();
//            $user->email = $request->email;
//            $user->phone = $request->phone;
//            $user->f_name = $request->first_name;
//            $user->l_name = $request->last_name;
//            $user->street_address = $request->address;
//
//            $baseUsername = strstr($request->email, '@', true);
//            $username = $baseUsername;
//            $counter = 1;
//            while (User::where('name', $username)->exists()) {
//                $username = $baseUsername . '_' . $counter++;
//            }
//            $user->name = $username;
//
//            // Save hashed + encrypted passwords
//            $user->password = Hash::make($request->password);
//            $user->wp_password = Crypt::encryptString($request->input('password')); // store for WP use later
//
//            $user->save();
//
//            // Create user in WordPress
//            $displayName = $request->first_name . ' ' . $request->last_name;
//            $wpResponse = Http::asForm()->post(
//                'https://maximfood.com/?rest_route=/simple-jwt-login/v1/users',
//                [
//                    'email' => $request->email,
//                    'password' => $request->password,
//                    'display_name' => $displayName,
//                    'user_url' => $request->phone,
//                ]
//            );
//
//            \Log::info('WordPress Response:', [
//                'status' => $wpResponse->status(),
//                'body' => $wpResponse->body(),
//                'json' => $wpResponse->json(),
//            ]);
//
//            if (!$wpResponse->successful()) {
//                throw new \Exception('WordPress user creation failed: ' . $wpResponse->body());
//            }
//
//            DB::commit();
//
//            auth()->login($user);
//            $tokenResult = $user->createToken('token');
//            $user->token = 'Bearer ' . $tokenResult->accessToken;
//
//            return $this->responseMsg('Registration successful', UserDataResource::make($user), 200);
//        } catch (\Exception $e) {
//            DB::rollBack();
//            \Log::error('User registration failed: ' . $e->getMessage());
//            return $this->responseMsg('User registration failed: ' . $e->getMessage(), null, 500);
//        }
//    }


    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'      => 'required|unique:users',
            'first_name' => 'required',
            'last_name'  => 'required',
            'phone'      => ['required', 'unique:users', 'regex:/^\d{10,15}$/'],
            'password'   => 'required|min:8',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            $errorMessage = implode(' ', $errors);
            return $this->responseMsg($errorMessage, null, 500);
        }

        DB::beginTransaction();
        try {
            $user = new User();
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->f_name = $request->first_name;
            $user->l_name = $request->last_name;
            $user->street_address = $request->address;

            $baseUsername = strstr($request->email, '@', true);
            $username = $baseUsername;
            $counter = 1;
            while (User::where('name', $username)->exists()) {
                $username = $baseUsername . '_' . $counter++;
            }
            $user->name = $username;

            $user->password = Hash::make($request->password);
            $user->wp_password = Crypt::encryptString($request->input('password'));

            $user->save();

            // WP creation (kept as you had it)
            $displayName = $username;
            $wpResponse = Http::asForm()->post(
                'https://maximfood.com/?rest_route=/simple-jwt-login/v1/users',
                [
                    'email' => $request->email,
                    'password' => $request->password,
                    'display_name' => $displayName,
                    'user_url' => $request->phone,
                ]
            );

            \Log::info('WordPress Response:', [
                'status' => $wpResponse->status(),
                'body' => $wpResponse->body(),
                'json' => $wpResponse->json(),
            ]);

            if (!$wpResponse->successful()) {
                throw new \Exception('WordPress user creation failed: ' . $wpResponse->body());
            }

            DB::commit();

            // Generate JWT token (tymon/jwt-auth)
            $token = JWTAuth::fromUser($user); // or auth()->login($user) if guard set to jwt

            $user->token = 'Bearer ' . $token;

            return $this->responseMsg('Registration successful', UserDataResource::make($user), 200);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('User registration failed: ' . $e->getMessage());
            return $this->responseMsg('User registration failed: ' . $e->getMessage(), null, 500);
        }
    }


    public function loginWithSocial(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'first_name' => 'required|string|max:255',
            'social_type' => 'required|string|in:google,facebook,apple',
            'image' => 'nullable',
        ], [
            'email.required' => 'The email is required',
            'email.email' => 'The email must be a valid email address',
            'first_name.required' => 'The first name is required',
            'first_name.string' => 'The first name must be a string',
            'first_name.max' => 'The first name must not be greater than 255 characters',
            'social_type.required' => 'The social type is required',
            'social_type.string' => 'The social type must be a string',
            'social_type.in' => 'The social type must be one of the following: google, facebook, apple',
        ]);
        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            $errorMessage = implode(' ', $errors);
            return $this->responseMsg($errorMessage, null, 403);
        }

        $user = User::where('email', $request['email'])->first();
        if ($user) {
            auth()->login( $user);
            // $tokenResult = $user->createToken('token');
            // $user->token = 'Bearer ' . $tokenResult->accessToken;
            $token = JWTAuth::fromUser($user);
            $user->token = 'Bearer ' . $token;
            $user->is_registered = true;
            return $this->responseMsg('logged in successfully', new UserDataResource($user), 200);
        } else {
            $user = new User();
            $user->email = $request['email'];
            $user->f_name = $request['first_name'];
            $user->l_name = $request['last_name'] ?? '';
            $user->password = Hash::make(Str::random(16));
            $user->social_type = $request['social_type'];
            $user->phone = $request['phone'] ?? null;
//            $user->image = $request['image'] ?? null;
            $baseUsername = strstr($request->input('email'), '@', true);
            $username = $baseUsername;
            $counter = 1;
            while (User::where('name', $username)->exists()) {
                $username = $baseUsername . '_' . $counter;
                $counter++;
            }
            $user->name = $username;
            $user->save();

            auth()->login($user);
            $tokenResult = $user->createToken('token');
            // $user->token = 'Bearer ' . $tokenResult->accessToken;
            // $user->is_registered = false;
            $token = JWTAuth::fromUser($user);
            $user->token = 'Bearer ' . $token;
            return $this->responseMsg('registered successfully', new UserDataResource($user), 200);
        }


    }

//    public function login(Request $request): JsonResponse
//    {
//        $credentials = $request->only('identity', 'password');
//
//        $user = User::where('name', $request->identity)
//                    ->orWhere('phone', $request->identity)
//                    ->first();
//
//        if (!$user || !Hash::check($request->password, $user->password)) {
//            return $this->responseMsg('Invalid credentials', null, 401);
//        }
//
//        $token = JWTAuth::fromUser($user);
//
//        $user->token = 'Bearer ' . $token;
//
//        return $this->responseMsg("you have logged in successfully", UserDataResource::make($user), 200);
//    }




    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'identity' => 'required',
            'login_type' => 'nullable|in:username,phone',
            'password' => 'required',
        ]);

        if($request->login_type == 'phone'){
            $user = User::where('phone', $request->identity)->first();
        }elseif ($request->login_type == 'username'){
            $user = User::where('name', $request->identity)->first();
        }else{//temporary for application
            $user = User::where('phone', $request->identity)->first();
        }

        // 🔹 Step 1: Send login request to WordPress API
        $response = Http::asForm()->post('https://maximfood.com/?rest_route=/simple-jwt-login/v1/auth', [
            'email' => $user->email,
            'password' => $request->password,
        ]);
        
        
        // 🔹 Step 2: Check if the login failed
        if (!$response->successful()) {
            return $this->responseMsg('Invalid credentials', null, 401);
        }




        $data = $response->json();

        // WordPress Simple-JWT-Login returns something like:
        // { "data": { "jwt": "token_here", ... }, "code": "jwt_auth_valid_credential" }

        if (!isset($data['data']['jwt'])) {
            return $this->responseMsg('Invalid credentials', null, 401);
        }

        $wpToken = $data['data']['jwt'];

//        // 🔹 Step 3: Sync or find the user locally (optional)
//        $user = User::firstOrCreate(
//            ['email' => $request->identity],
//            ['name' => $request->identity]
//        );

        // 🔹 Step 4: Generate a Laravel JWT to keep user session consistent
        $localToken = JWTAuth::fromUser($user);
        $user->token = 'Bearer ' . $localToken;
        $user->wp_token = $wpToken; // store WP token if needed

        // 🔹 Step 5: Return success response
        return $this->responseMsg('You have logged in successfully', UserDataResource::make($user), 200);
    }







    public function logout(Request $request): JsonResponse
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());
            $deviceToken = DeviceToken::where('token', $request->input('device_token'))->first();
            if ($deviceToken) {
                $deviceToken->delete();
            }
            return $this->responseMsg('Successfully logged out', null, 200);
        } catch (\Exception $e) {
            return $this->responseMsg('Failed to logout, please try again', null, 500);
        }
    }

//    public function resetPasswordRequest(Request $request)
//    {
//        $validator = Validator::make($request->all(), [
//            'identity' => 'required|min:6', // This can be email or phone number
//        ]);
//
//        if ($validator->fails()) {
//            return $this->responseMsg(Helpers::validationErrorProcessor($validator), null, 403);
//        }
//
//        $identity = $request->input('identity');
//        if (is_numeric($identity)) {
//            $user = User::where('phone', 'like', "%{$identity}%")->first();
//        } else {
//            $user = User::where('email', $identity)->first();
//            if (!$user) {
//                return $this->responseMsg('User not found!', null, 404);
//            }
//
//
//
//
//        //            /simple-jwt-login/v1/user/reset_password
//        // Step 1: Get JWT from WordPress
//        $resetPasswordResponse = Http::asForm()->post('https://maximfood.com/?rest_route=/simple-jwt-login/v1/user/reset_password', [
//            'email' => $user->email,
//        ]);
//            dd($resetPasswordResponse);
////        dd(
////            $email,
////            $password,
////            $resetPasswordResponse->status(),
////            $resetPasswordResponse->body(),
////            $resetPasswordResponse->json(),
////        );
//
//
//
//        if (!$resetPasswordResponse->ok() || !$resetPasswordResponse['success']) {
//            return response()->json(['message' => 'Failed to authenticate with WordPress'], 400);
//        }
//
////        $jwt = $loginResponse['data']['jwt'];
////
////        // Step 2: Delete from WordPress
////        $deleteResponse = Http::asForm()->delete('https://maximfood.com/?rest_route=/simple-jwt-login/v1/users', [
////            'JWT' => $jwt,
////        ]);
////
////        if (!$deleteResponse->ok() || !$deleteResponse['success']) {
////            return response()->json([
////                'message' => 'Failed to delete user from WordPress',
////                'response' => $deleteResponse->json(),
////            ], 400);
////        }
////
////        // Step 3: Delete locally
////        User::where('email', $email)->delete();
////
////        return response()->json([
////            'message' => 'User deleted successfully from both systems',
////        ]);
////
////
////
////        $user = auth()->user();
////        if (!$user) {
////            return $this->responseMsg('User not found.', null, 404);
////        }
////        try {
////            $user->delete();
////            return $this->responseMsg('User has been deleted successfully.', null, 200);
////        } catch (\Exception $e) {
////            return $this->responseMsg('Failed to delete user!', null, 500);
////        }
//
////            https://maximfood.com/?rest_route=/simple-jwt-login/v1/user/reset_password&email=aaamm@gmail.com
//
//
//
//
//
//
//
//
//            // $otp = rand(100000, 999999);
////            $otp = 1111;
////
////            // Send OTP via email if identity is email
////            if (filter_var($identity, FILTER_VALIDATE_EMAIL)) {
//////                try {
////                    Mail::to($identity)->send(new SendOtpMail([
////                        'email' => $identity,
////                        'otp' => $otp
////                    ]));
//////                } catch (\Exception $e) {
//////                    return $this->responseMsg('Failed to send OTP email.', null, 500);
//////                }
////            }
//        }
//
//        return $this->responseMsg('OTP has been sent successfully!', ['identity' => $request['identity'], 'otp' => '1111'], 200);
//    }





    public function resetPasswordRequest(Request $request)
    {
        // Step 1: Validate input
        $validator = Validator::make($request->all(), [
            'identity' => 'required|min:6', // can be email or phone
        ]);

        if ($validator->fails()) {
            return $this->responseMsg(Helpers::validationErrorProcessor($validator), null, 403);
        }

        $identity = $request->input('identity');

        // Step 2: Find user in local database
        if (is_numeric($identity)) {
            $user = User::where('phone', 'like', "%{$identity}%")->first();
        } else {
            $user = User::where('email', $identity)->first();
        }

        if (!$user) {
            return $this->responseMsg('User not found!', null, 404);
        }

        // Step 3: Request WordPress password reset via Simple JWT Login
        $resetPasswordResponse = Http::asForm()->post(
            'https://maximfood.com/?rest_route=/simple-jwt-login/v1/user/reset_password',
            [
                'email' => $user->email, // ✅ correct parameter name
            ]
        );

        // Step 4: Log or debug the response (optional)
        \Log::info('WP Reset Password Response', [
            'status' => $resetPasswordResponse->status(),
            'body' => $resetPasswordResponse->body(),
            'json' => $resetPasswordResponse->json(),
        ]);

        // Step 5: Handle errors
        if (!$resetPasswordResponse->ok() || !$resetPasswordResponse->json('success')) {
            return response()->json([
                'message' => 'Failed to request password reset from WordPress',
                'response' => $resetPasswordResponse->json(),
            ], 400);
        }

        // Step 6: Return success response
        return $this->responseMsg(
            'Password reset request sent successfully!',
            [
                'email' => $user->email,
                'wp_response' => $resetPasswordResponse->json(),
            ],
            200
        );
    }









//    public function reset_password_request()
//    {
//        $user=auth()->user();
//        if (!$user) {
//            return $this->responseMsg("Unauthorized", null, 401);
//        }else{
//
//        }
//        return $this->responseMsg(
//            "The wishlist has been returned successfully",
//            null,
//            200
//        );
//    }

//    public function resetPasswordSubmit(Request $request)
//    {
//        $validator = Validator::make($request->all(), [
//            'identity' => 'required|string',
//            'new_password' => 'required|min:8',
//        ]);
//
//        if ($validator->fails()) {
//            return $this->responseMsg( Helpers::validationErrorProcessor($validator),null, 403);
//        }
//
//
//        try {
//            $identity = $request->input('identity');
//            $newPassword = $request->input('new_password');
//
//
//            if (is_numeric($identity)) {
//                $user = User::where('phone', 'like', "%{$identity}%")->first();
//            } else {
//                $user = User::where('email', $identity)->first();
//            }
//
//            if (!$user) {
//                return $this->responseMsg( 'User not found!',null, 404);
//            }
//
//
//
//
//            $user->password = bcrypt(str_replace(' ', '', $newPassword));
//            $user->save();
//
//            $token = JWTAuth::fromUser($user);
//
//            $user->token = 'Bearer ' . $token;
//            return $this->responseMsg(
//                'the password has been reset successfully',
//
//                     UserDataResource::make($user)
//                ,
//                200
//            );
//        } catch (\Exception $e) {
//            return $this->responseMsg('reset password has been failed!', null, 500);
//        }
//    }






    public function resetPasswordSubmit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identity' => 'required|string', // email or phone
            'code' => 'required|string', // reset code from email
            'new_password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            return $this->responseMsg(Helpers::validationErrorProcessor($validator), null, 403);
        }

        $identity = $request->input('identity');
        $code = $request->input('code');
        $newPassword = $request->input('new_password');

        // Step 1: Find the user locally
        if (is_numeric($identity)) {
            $user = User::where('phone', 'like', "%{$identity}%")->first();
        } else {
            $user = User::where('email', $identity)->first();
        }

        if (!$user) {
            return $this->responseMsg('User not found!', null, 404);
        }

        // Step 2: Call WordPress API to verify code and reset password
        $wpResponse = Http::asForm()->put(
            'https://maximfood.com/?rest_route=/simple-jwt-login/v1/user/reset_password',
            [
                'email' => $user->email,
                'code' => $code,
                'new_password' => $newPassword,
            ]
        );

        // Step 3: Log WordPress response for debugging
        \Log::info('WP Password Reset Submit Response', [
            'status' => $wpResponse->status(),
            'body' => $wpResponse->body(),
            'json' => $wpResponse->json(),
        ]);

        // Step 4: Handle WordPress failure
        if (!$wpResponse->ok() || !$wpResponse->json('success')) {
            return response()->json([
                'message' => 'Failed to reset password on WordPress',
                'response' => $wpResponse->json(),
            ], 400);
        }

        // Step 5: Update password locally
        $user->password = Hash::make($newPassword);
        $user->save();

        // Step 6: Generate new local JWT token
        $token = JWTAuth::fromUser($user);
        $user->token = 'Bearer ' . $token;

        return $this->responseMsg(
            'Password has been reset successfully!',
            UserDataResource::make($user),
            200
        );
    }






    public function deleteUser(Request $request)
    {
        $email = $request->input('email');
        $password = $request->input('password'); // to authenticate in WP

        // Step 1: Get JWT from WordPress
        $loginResponse = Http::asForm()->post('https://maximfood.com/?rest_route=/simple-jwt-login/v1/auth', [
            'email' => $email,
            'password' => $password,
        ]);

        if (!$loginResponse->ok() || !$loginResponse['success']) {
            return response()->json(['message' => 'Failed to authenticate with WordPress'], 400);
        }

        $jwt = $loginResponse['data']['jwt'];

        // Step 2: Delete from WordPress
        $deleteResponse = Http::asForm()->delete('https://maximfood.com/?rest_route=/simple-jwt-login/v1/users', [
            'JWT' => $jwt,
        ]);

        if (!$deleteResponse->ok() || !$deleteResponse['success']) {
            return response()->json([
                'message' => 'Failed to delete user from WordPress',
                'response' => $deleteResponse->json(),
            ], 400);
        }

        // Step 3: Delete locally
        User::where('email', $email)->delete();

        return response()->json([
            'message' => 'User deleted successfully from both systems',
        ]);
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
