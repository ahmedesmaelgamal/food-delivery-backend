<?php

namespace App\Services;

use App\Mail\ContactUsMail;
use App\Models\Contact;
use Illuminate\Support\Facades\Mail;

class ContactUsService
{
    public function __construct(
        protected Contact $contactModel,
    ) {}
    public function send($request)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'message' => 'nullable|string',
            'mobile_number' => 'nullable|string|max:20', 
            'subject' => 'nullable|string|max:255',
        ]);

        if(!$request->filled("message")){
            return response()->json([
                'msg' => 'Message field is required.',
                "data" => [],
                'status' => 400
            ], 400);
        }
        
        if(!$request->filled("mobile_number")){
            return response()->json([
                'msg' => 'mobile_number field is required.',
                "data" => [],
                'status' => 400
            ], 400);
        }
        
        if(!$request->filled("subject")){
            return response()->json([
                'msg' => 'subject field is required.',
                "data" => [],
                'status' => 400
            ], 400);
        }


        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'mobile_number' => $request->mobile_number,
            'message' => $request->message,
            'subject'  => $request->subject,
            'seen' => false,
            'reply' => null,
        ];


        try {
            $this->contactModel->create($data);

            Mail::to("support@maximfood.com")->queue(new ContactUsMail($data));

            return response()->json([
                'msg' => 'Message sent successfully.',
                "data" => [],
                'status' => 200
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'msg' => 'filed to send message .' . $e->getMessage(),
                "data" => [],
                'status' => 500
            ], 500);
        }
    }
}
