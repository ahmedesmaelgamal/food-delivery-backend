<?php

namespace App\Http\Controllers\RestAPI\v5\user\profile;


use App\Http\Controllers\Controller;
use App\Http\Resources\RestAPI\v5\UserAddressResource;
use App\Http\Resources\RestAPI\v5\UserResource;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Wishlist;
use App\Utils\Helpers;


class UserAddressController extends Controller
{

    public function getData(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                "msg" => 'User not authenticated',
                'data' => [],
                "status" => 401
            ], 401);
        }

        try {
            $userAddresses = UserAddress::where('user_id', $user->id)->orderBy('id', 'desc')->get();

            return response()->json([
                "msg" => 'success',
                'data' => UserAddressResource::collection($userAddresses),
                 "status" => 200
            ], 200);


        } catch (\Exception $e) {
            return response()->json([
                "msg" => 'Failed to get user addresses!',
                'data' => [],
                "status" => 500
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                "msg" => 'User not authenticated',
                'data' => [],
                'status' => 401
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'building_number' => 'required|string|max:255',
            'flat_number' => 'required|string|max:255',
            'city_id' => 'required|max:255',
            'area_id' => 'required|max:255',
            'postal_code' => 'nullable|string|max:255',
//            'country' => 'nullable|string|max:255'

            'floor_number' => 'required|string|max:255',
            'phone_number' => 'required|string|max:255',
            'address' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "msg" => 'Validation failed',
                'data' => $validator->errors()->all(),
                'status' => 422
            ], 422);
        }

//        try {
            $userAddress = UserAddress::create([
                'user_id' => $user->id,
                'building_number' => $request->building_number,
                'flat_number' => $request->flat_number,
                'city_id' => $request->city_id,
                'area_id' => $request->area_id,
                'postal_code' => $request->postal_code,

                'floor_number' => $request->floor_number,
                'phone_number' => $request->phone_number,
                'address' => $request->address
            ]);


            return response()->json([
                "msg" => 'User address created successfully',
                'data' => UserAddressResource::make($userAddress),
                'status' => 200
            ], 200);

//        } catch (\Exception $e) {
//            return $this->responseMsg(
//                'Failed to create user address',
//                null,
//                 500
//            );
//        }
    }
    public function responseMsg($msg, $data = null, int $status = 200): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'data' => $data,
            'msg' => $msg,
            'status' => $status
        ]);
    }
    public function update(Request $request, $id)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                "msg" => 'User not authenticated',
                'data' => [],
                'status' => 401
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'building_number' => 'required|string|max:255',
            'flat_number' => 'required|string|max:255',
            'city_id' => 'required|string|max:255',
            'area_id' => 'required|string|max:255',
            'postal_code' => 'required|string|max:255',

            'floor_number' => 'required|string|max:255',
            'phone_number' => 'required|string|max:255',
            'address' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                "msg" => 'Validation failed',
                'data' => [],
                'status' => 422
            ], 422);
        }

        try {
            $userAddress = UserAddress::where('user_id', $user->id)->find($id);

            if (!$userAddress) {
                return response()->json([
                    "msg" => 'User address not found',
                    'data' => [],
                    'status' => 404
                ], 404);
            }
//            dd($request->all());
            $userAddress->update([
                'user_id' => $user->id,
                'building_number' => $request->building_number,
                'flat_number' => $request->flat_number,
                'city_id' => $request->city_id,
                'area_id' => $request->area_id,
                'postal_code' => $request->postal_code,


                'floor_number' => $request->floor_number,
                'phone_number' => $request->phone_number,
                'address' => $request->address
            ]);
//            dd($userAddress);

            return response()->json([
                "msg" => 'User address updated successfully',
                'data' => new UserAddressResource($userAddress),
                'status' => 200
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                "msg" => 'Failed to update user address',
                'data' => [],
                'status' => 500
            ], 500);
        }
    }

    public function destroy($id)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                "msg" => 'User not authenticated',
                'data' => [],
                'status' => 401
            ], 401);
        }

        try {
            $userAddress = UserAddress::where('user_id', $user->id)->find($id);

            if (!$userAddress) {
                return response()->json([
                    "msg" => 'User address not found',
                    'data' => [],
                    'status' => 404
                ], 404);
            }

            $userAddress->delete();

            return response()->json([
                "msg" => 'User address deleted successfully',
                'data' => [],
                'status' => 200
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                "msg" => 'Failed to delete user address',
                'data' => [],
                'status' => 500
            ], 500);
        }
    }


}
