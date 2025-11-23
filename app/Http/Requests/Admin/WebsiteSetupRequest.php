<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Request;
use App\Traits\ResponseHandler;

class WebsiteSetupRequest extends Request
{
    use ResponseHandler;
    protected $stopOnFirstFailure = true;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_web_logo' => 'sometimes|mimes:jpg,jpeg,png,gif|max:1024|dimensions:min_width=325,min_height=100',
            'company_footer_logo' => 'sometimes|mimes:jpg,jpeg,png,gif|max:1024|dimensions:min_width=325,min_height=100',
            'company_fav_icon' => 'sometimes|mimes:jpg,jpeg,png,gif|max:1024',
            'loader_gif' => 'sometimes|mimes:jpg,jpeg,png,gif|max:1024',
            'company_mobile_logo' => 'sometimes|mimes:jpg,jpeg,png,gif|max:1024|dimensions:min_width=100,min_height=60',
        ];
    }

}
