<?php

namespace App\Http\Controllers\RestAPI\v5\user\contactUs;


use App\Http\Controllers\Controller;
use App\Http\Requests\Request;
use App\Mail\ContactUsMail;
use App\Services\ContactService;
use App\Services\ContactUsService;
use Illuminate\Support\Facades\Mail;

class ContactUsController extends Controller
{
    public function __construct(
        protected ContactUsService $contactService,
    )
    {
    }
    public function send(Request $request)
    {
        return $this->contactService->send($request);
    }
}
