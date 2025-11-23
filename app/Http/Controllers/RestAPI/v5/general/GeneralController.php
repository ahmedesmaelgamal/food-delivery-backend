<?php

namespace App\Http\Controllers\RestAPI\v5\general;


use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\DeviceToken;

class GeneralController extends Controller
{
    public function storeFcmToken(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'device_token'    => 'required',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            $errorMessage = implode(' ', $errors);
            return $this->responseMsg($errorMessage, null, 500);
        }
        try {
            $deviceToken = DeviceToken::where('token', $request->input('device_token'))->first();
            if ($deviceToken && $deviceToken->user_id === auth()->user()->id) {
                return $this->responseMsg('Device token already exists', null, 200);
            }
            $device_token = new DeviceToken();
            $device_token->user_id = auth()->user()->id;
            $device_token->token = $request->input('device_token');
            $device_token->save();
            return $this->responseMsg('Device token has been stored successfully', null, 200);
        } catch (\Exception $e) {
            return $this->responseMsg('Failed to store device token', null, 500);
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
