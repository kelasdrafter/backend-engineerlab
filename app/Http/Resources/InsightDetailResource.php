<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InsightDetailResource extends JsonResource
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
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'view_count' => $this->view_count,
            'comment_count' => $this->comment_count,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'photo_url' => $this->user->photo_url ?? null,
                'occupation' => $this->user->occupation ?? null,
            ],
            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
                'icon' => $this->category->icon,
                'description' => $this->category->description,
            ],
            'media' => [
                'videos' => $this->getMediaByType('video'),
                'images' => $this->getMediaByType('image'),
                'files' => $this->getMediaByType('file'),
            ],
        ];
    }

    /**
     * Get media by type
     */
    private function getMediaByType(string $type): array
    {
        return $this->media
            ->where('type', $type)
            ->sortBy('order')
            ->map(function ($media) {
                return [
                    'id' => $media->id,
                    'file_name' => $media->file_name,
                    'url' => $media->url,
                    'file_size' => $media->file_size,
                    'mime_type' => $media->mime_type,
                    'order' => $media->order,
                ];
            })
            ->values()
            ->toArray();
    }
}