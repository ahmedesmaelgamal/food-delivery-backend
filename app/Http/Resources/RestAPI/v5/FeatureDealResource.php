<?php

namespace App\Http\Resources\RestAPI\v5;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Category;
class FeatureDealResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
//        $category_name=null;
//        $category_id = $this->additional['lang'] =='en'? $this->category_id_en: $this->category_id_ar ;
//        if ($this->type == 'category'){
//            $category_name=Category::where('wordpress_id',$category_id)->first()?->name;
//        }
//        return [
//            'id' => $this->id,
//            'url' => $this->url,
//            'type' => $this->type,
//            'category_id'=> $category_id ,
//            'category_name' => $category_name,
////            'category_id_ar'=>$this->category_id_ar ? Category::where('wordpress_id',$this->category_id_ar)->first()?->id : null,
//        ];


        $lang = $request->header('Accept-Language', 'en');

        $category_id = $lang == 'en' ? $this->category_id_en : $this->category_id_ar;
        $category_name = null;

        if ($this->type === 'category') {
            $category_name = Category::where('wordpress_id', $category_id)->first()?->name;
        }

        return [
            'id' => $this->id,
            'url' => $this->url,
            'type' => $this->type,
            'category_id' => $category_id,
            'category_name' => $category_name,
        ];

    }
}
