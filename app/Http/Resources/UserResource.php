<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'bio' => $this->bio,
            'phone' => $this->phone,
            'city' => $this->city,
            'birthdate' => $this->birthdate,
            'photo_url' => $this->photo_url,
            'occupation' => $this->occupation,
            'institution' => $this->institution,
            'role' => $this->role,
            'email_verified_at' => $this->email_verified_at,
            'is_active' => $this->is_active,
            'total_transactions' => $this->transactions()->where('status', 'success')->count(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
