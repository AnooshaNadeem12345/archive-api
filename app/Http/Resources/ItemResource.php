<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'file_url' => $this->file_url,
            'file_type' => $this->file_type,
            'category' => $this->category,
            'tags' => $this->tags ?? [],
            'uploader' => new UserResource($this->whenLoaded('uploader')),
            'collections' => CollectionResource::collection($this->whenLoaded('collections')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
