<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InsightCommentResource extends JsonResource
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
            'comment' => $this->comment,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'photo_url' => $this->user->photo_url ?? null,
            ],
            'mentions' => $this->mentionedUsers->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                ];
            }),
            'media' => $this->when(
                $this->relationLoaded('media'),
                function () {
                    return [
                        'images' => $this->images->map(function ($media) {
                            return [
                                'id' => $media->id,
                                'url' => $media->url,
                                'file_name' => $media->file_name,
                                'file_size' => $media->file_size,
                                'mime_type' => $media->mime_type,
                            ];
                        }),
                        'videos' => $this->videos->map(function ($media) {
                            return [
                                'id' => $media->id,
                                'url' => $media->url,
                                'file_name' => $media->file_name,
                                'file_size' => $media->file_size,
                                'mime_type' => $media->mime_type,
                            ];
                        }),
                        'files' => $this->files->map(function ($media) {
                            return [
                                'id' => $media->id,
                                'url' => $media->url,
                                'file_name' => $media->file_name,
                                'file_size' => $media->file_size,
                                'mime_type' => $media->mime_type,
                            ];
                        }),
                    ];
                }
            ),
            'replies_count' => $this->replies->count(),
            'replies' => $this->when(
                $this->relationLoaded('replies') && $this->replies->isNotEmpty(),
                function () {
                    return $this->replies->map(function ($reply) {
                        return [
                            'id' => $reply->id,
                            'comment' => $reply->comment,
                            'created_at' => $reply->created_at?->toIso8601String(),
                            'updated_at' => $reply->updated_at?->toIso8601String(),
                            'user' => [
                                'id' => $reply->user->id,
                                'name' => $reply->user->name,
                                'photo_url' => $reply->user->photo_url ?? null,
                            ],
                            'mentions' => $reply->mentionedUsers->map(function ($user) {
                                return [
                                    'id' => $user->id,
                                    'name' => $user->name,
                                ];
                            }),
                            'media' => $this->when(
                                $reply->relationLoaded('media'),
                                function () use ($reply) {
                                    return [
                                        'images' => $reply->images->map(function ($media) {
                                            return [
                                                'id' => $media->id,
                                                'url' => $media->url,
                                                'file_name' => $media->file_name,
                                                'file_size' => $media->file_size,
                                                'mime_type' => $media->mime_type,
                                            ];
                                        }),
                                        'videos' => $reply->videos->map(function ($media) {
                                            return [
                                                'id' => $media->id,
                                                'url' => $media->url,
                                                'file_name' => $media->file_name,
                                                'file_size' => $media->file_size,
                                                'mime_type' => $media->mime_type,
                                            ];
                                        }),
                                        'files' => $reply->files->map(function ($media) {
                                            return [
                                                'id' => $media->id,
                                                'url' => $media->url,
                                                'file_name' => $media->file_name,
                                                'file_size' => $media->file_size,
                                                'mime_type' => $media->mime_type,
                                            ];
                                        }),
                                    ];
                                }
                            ),
                        ];
                    });
                }
            ),
        ];
    }
}