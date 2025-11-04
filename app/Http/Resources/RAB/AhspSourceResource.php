<?php

namespace App\Http\Resources\RAB;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AhspSourceResource extends JsonResource
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
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'icon' => $this->icon,
            'color' => $this->color,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
            
            // Relationships (loaded conditionally)
            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                    'email' => $this->creator->email,
                ];
            }),
            
            // Statistics (when requested)
            'statistics' => $this->when($request->include_stats, function () {
                return [
                    'total_master_ahsp' => $this->masterAhsp()->count(),
                    'total_projects' => $this->projects()->count(),
                    'total_templates' => $this->projectTemplates()->count(),
                ];
            }),
            
            // Timestamps
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'deleted_at' => $this->when($this->deleted_at, $this->deleted_at?->toIso8601String()),
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
                'resource_type' => 'ahsp_source',
            ],
        ];
    }
}
