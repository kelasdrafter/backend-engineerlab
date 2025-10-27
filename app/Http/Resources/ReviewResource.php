<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'review' => $this->review,
            'rating' => $this->rating,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'photo_url' => $this->user->photo_url,
            ],
            'batch' => [
                'id' => $this->batch->id,
                'name' => $this->batch->name,
                'course_id' => $this->batch->course_id,
                'start_date' => $this->batch->start_date,
            ],
        ];
    }
}
