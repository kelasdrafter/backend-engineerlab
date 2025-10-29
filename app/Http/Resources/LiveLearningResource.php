<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LiveLearningResource extends JsonResource
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
            'thumbnail_url' => $this->thumbnail_url,
            'description' => $this->description,
            'schedule' => $this->schedule,
            'materials' => $this->materials,
            'is_paid' => $this->is_paid,
            'price' => $this->price,
            'zoom_link' => $this->zoom_link,
            'community_group_link' => $this->community_group_link,
            'max_participants' => $this->max_participants,
            'status' => $this->status,
            
            // Computed fields
            'registrations_count' => $this->when(
                $this->relationLoaded('registrations') || isset($this->registrations_count),
                function () {
                    return $this->registrations_count ?? $this->registrations->count();
                }
            ),
            'remaining_slots' => $this->when(
                !$request->routeIs('admin.*'), // Only for public API
                $this->getRemainingSlots()
            ),
            'is_registration_open' => $this->when(
                !$request->routeIs('admin.*'), // Only for public API
                $this->isRegistrationOpen()
            ),
            
            // Admin-only fields
            'creator' => $this->when(
                $request->routeIs('admin.*') && $this->relationLoaded('creator'),
                function () {
                    return [
                        'id' => $this->creator->id,
                        'name' => $this->creator->name,
                        'email' => $this->creator->email,
                    ];
                }
            ),
            'created_by' => $this->when($request->routeIs('admin.*'), $this->created_by),
            'updated_by' => $this->when($request->routeIs('admin.*'), $this->updated_by),
            'deleted_by' => $this->when($request->routeIs('admin.*'), $this->deleted_by),
            
            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->when(
                $request->routeIs('admin.*'),
                $this->deleted_at?->toISOString()
            ),
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