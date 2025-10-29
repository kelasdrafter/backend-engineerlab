<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LiveLearningRegistrationResource extends JsonResource
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
            'live_learning_id' => $this->live_learning_id,
            
            // Live Learning info (if loaded)
            'live_learning' => $this->when(
                $this->relationLoaded('liveLearning'),
                function () {
                    return [
                        'id' => $this->liveLearning->id,
                        'title' => $this->liveLearning->title,
                        'slug' => $this->liveLearning->slug,
                        'schedule' => $this->liveLearning->schedule,
                        'status' => $this->liveLearning->status,
                    ];
                }
            ),
            
            // Registration data
            'name' => $this->name,
            'email' => $this->email,
            'whatsapp' => $this->whatsapp,
            
            // Computed fields
            'formatted_whatsapp' => $this->formatted_whatsapp,
            'whatsapp_link' => $this->whatsapp_link,
            
            // Timestamps
            'registered_at' => $this->registered_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'message' => 'Data retrieved successfully',
                'code' => 200,
            ],
        ];
    }
}