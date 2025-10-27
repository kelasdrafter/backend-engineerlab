<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WaitingListResource extends JsonResource
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
            'user' => $this->user,
            'course' => $this->course,
            'batch' => $this->batch ? [
                "id" => $this->batch->id,
                "name" => $this->batch->name,
                "start_date" => $this->batch->start_date,
                "whatsapp_group_url" => $this->batch->whatsapp_group_url,
            ] : null,
        ];
    }
}
