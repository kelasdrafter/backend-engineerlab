<?php

namespace App\Http\Resources\RAB;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MasterAhspResource extends JsonResource
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
            'unit' => $this->unit,
            'description' => $this->description,
            'is_active' => $this->is_active,
            
            // Full code with source prefix
            'full_code' => $this->when($this->relationLoaded('ahspSource'), function () {
                return $this->full_code;
            }),
            
            // AHSP Source
            'ahsp_source' => $this->whenLoaded('ahspSource', function () {
                return [
                    'id' => $this->ahspSource->id,
                    'code' => $this->ahspSource->code,
                    'name' => $this->ahspSource->name,
                    'icon' => $this->ahspSource->icon,
                    'color' => $this->ahspSource->color,
                ];
            }),
            
            // Composition items (grouped by category)
            'composition' => $this->whenLoaded('items', function () {
                return [
                    'materials' => $this->items->where('category', 'material')->map(function ($item) {
                        return $this->formatCompositionItem($item);
                    })->values(),
                    'labor' => $this->items->where('category', 'labor')->map(function ($item) {
                        return $this->formatCompositionItem($item);
                    })->values(),
                    'equipment' => $this->items->where('category', 'equipment')->map(function ($item) {
                        return $this->formatCompositionItem($item);
                    })->values(),
                    'summary' => [
                        'total_materials' => $this->items->where('category', 'material')->count(),
                        'total_labor' => $this->items->where('category', 'labor')->count(),
                        'total_equipment' => $this->items->where('category', 'equipment')->count(),
                        'total_items' => $this->items->count(),
                    ],
                ];
            }),
            
            // Creator
            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                ];
            }),
            
            // Usage statistics (when requested)
            'usage' => $this->when($request->include_usage, function () {
                return [
                    'total_in_projects' => $this->projectAhsp()->count(),
                    'total_in_templates' => $this->templateItems()->count(),
                ];
            }),
            
            // Timestamps
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'deleted_at' => $this->when($this->deleted_at, $this->deleted_at?->toIso8601String()),
        ];
    }

    /**
     * Format composition item data
     */
    protected function formatCompositionItem($ahspItem): array
    {
        return [
            'id' => $ahspItem->id,
            'category' => $ahspItem->category,
            'category_label' => $ahspItem->category_label,
            'coefficient' => (float) $ahspItem->coefficient,
            'sort_order' => $ahspItem->sort_order,
            'item' => [
                'id' => $ahspItem->item->id,
                'code' => $ahspItem->item->code,
                'name' => $ahspItem->item->name,
                'type' => $ahspItem->item->type,
                'type_label' => $ahspItem->item->type_label,
                'unit' => $ahspItem->item->unit,
            ],
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
                'resource_type' => 'master_ahsp',
            ],
        ];
    }
}
