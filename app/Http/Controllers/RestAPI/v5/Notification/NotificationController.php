<?php

namespace App\Http\Controllers\RestAPI\v5\Notification;

use App\Http\Controllers\Controller;
use App\Http\Requests\Request;
use App\Traits\FirebaseNotificationTrait;
use App\Models\Notification;
use Mockery\Matcher\Not;

class NotificationController extends Controller
{
    use FirebaseNotificationTrait;

    public function sendFcmNotification(Request $request)
    {
        if(!$request->has('user_ids') || empty($request->input('user_ids'))) {
            return $this->responseMsg('User IDs are required',null, 200);
        }

        if(!is_array($request->input('user_ids'))) {
            return $this->responseMsg('User IDs must be type of array', null, 200);
        }
        $notificationTitle = $request->input('title', 'Notification Title');
        $notificationBody = $request->input('body', 'Notification Body');
        if(empty($notificationTitle) || empty($notificationBody)) {
            return $this->responseMsg('Notification title and body are required', null, 200);
        }
        Notification::create([
            'title' => $notificationTitle,
            'body' => $notificationBody,
            'sent_to' => auth()->id(),
            'refrence_id' => $request->input('refrence_id'),
            'refrence_type' => $request->input('refrence_type'),
        ]);
        $data = [
            "title" => $notificationTitle,
            "body" => $notificationBody,
        ];
        $user_ids = request('user_ids', []);
        $additionalData = request('additionalData', []);

        return $this->sendFcm($data, $user_ids, $additionalData);
    }

    public function responseMsg($msg, $data = null, int $status = 200)
    {
        return response()->json([
            'data' => $data,
            'msg' => $msg,
            'status' => $status
        ]);
    }

}
