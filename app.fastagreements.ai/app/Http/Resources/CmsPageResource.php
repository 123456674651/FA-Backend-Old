<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CmsPageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'featured_image' => $this->featured_image ? asset('storage/cms/' . $this->featured_image) : null,
            'meta_title' => $this->meta_title,
            'meta_keywords' => $this->meta_keywords,
            'meta_description' => $this->meta_description,
            'status' => $this->status,
        ];

        // Format and conditionally include timestamps
        if ($request->routeIs('api.cms-pages.index') || $request->is('*api/cms-pages')) {
            $data['created_at'] = $this->created_at ? $this->created_at->toDateTimeString() : null;
            $data['updated_at'] = $this->updated_at ? $this->updated_at->toDateTimeString() : null;
        }

        return $data;
    }
}
