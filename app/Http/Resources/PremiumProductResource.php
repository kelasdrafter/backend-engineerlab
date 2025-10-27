<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PremiumProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'short_description' => $this->short_description,
            'price' => (float) $this->price,
            'discount_price' => (float) $this->discount_price,
            'final_price' => (float) $this->final_price,
            'discount_percentage' => $this->discount_percentage,
            'has_discount' => $this->has_discount,
            'thumbnail_url' => $this->thumbnail_url,
            'file_url' => $this->when(
                $request->user() && $request->user()->role === 'admin',
                $this->file_url
            ), // Only show file_url to admin
            'view_count' => $this->view_count,
            'purchase_count' => $this->purchase_count,
            'is_featured' => $this->is_featured,
            'is_active' => $this->is_active,
            'galleries' => $this->whenLoaded('galleries'),
            'videos' => $this->whenLoaded('videos'),
            'compatibilities' => $this->whenLoaded('compatibilities'),
            'qnas' => $this->whenLoaded('qnas'),
            'reviews' => $this->whenLoaded('reviews'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}