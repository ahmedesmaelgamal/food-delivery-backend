<?php

namespace App\Http\Resources\RestAPI\v5;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Category;
class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
//        dd($this->sub_categories);
//        if ($this->parent_id != 0) {
//            $sub_category = CategoryResource::collection($this->whenLoaded('sub_categories'));
//        }
            return [
                'id' => $this->wordpress_id,
                'name' => $this->name,
                'slug' => $this->slug,
                'icon' => asset($this->icon) ?? null,
//                'sub_category' => CategoryResource::collection($this->whenLoaded('sub_categories')) ?? null
                'sub_category' => CategoryResource::collection(Category::where('parent_id',$this->wordpress_id)->get()) ?? null
                // 'home_status' => $this->home_status,
                // 'parent_id' => $this->parent_id,
            ];

    }

    public function Products()
    {
        return $this->hasMany(\App\Models\Product::class, 'category_id');
    }
}
