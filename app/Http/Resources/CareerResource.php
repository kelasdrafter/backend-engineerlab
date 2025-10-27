<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CareerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'location' => $this->location,
            'is_active' => (bool) $this->is_active,
            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'value' => $this->category->value,
                'group' => $this->category->group,
                'is_active' => (bool) $this->category->is_active,
            ],
        ];
    }
}
