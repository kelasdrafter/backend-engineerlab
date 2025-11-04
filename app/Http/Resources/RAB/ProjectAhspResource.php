<?php

namespace App\Http\Resources\RAB;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectAhspResource extends JsonResource
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
            'source_type' => $this->source_type,
            
            // Full code with source prefix
            'full_code' => $this->when($this->relationLoaded('ahspSource'), function () {
                return $this->full_code;
            }),
            
            // Source type info
            'source_info' => [
                'is_from_master' => $this->isFromMaster(),
                'is_custom' => $this->isCustom(),
                'source_type' => $this->source_type,
                'source_type_label' => $this->source_type === 'master' ? 'Master AHSP' : 'Custom AHSP',
            ],
            
            // Project
            'project' => $this->whenLoaded('project', function () {
                return [
                    'id' => $this->project->id,
                    'name' => $this->project->name,
                    'status' => $this->project->status,
                ];
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
            
            // Master AHSP reference (if from master)
            'master_ahsp' => $this->whenLoaded('masterAhsp', function () {
                if (!$this->masterAhsp) {
                    return null;
                }
                return [
                    'id' => $this->masterAhsp->id,
                    'code' => $this->masterAhsp->code,
                    'name' => $this->masterAhsp->name,
                    'unit' => $this->masterAhsp->unit,
                    'is_active' => $this->masterAhsp->is_active,
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
            
            // Unit price calculation (when requested)
            'unit_price' => $this->when($request->include_price, function () {
                $price = $this->calculateUnitPrice();
                return [
                    'amount' => (float) $price,
                    'formatted' => 'Rp ' . number_format($price, 0, ',', '.'),
                ];
            }),
            
            // Breakdown (when requested)
            'breakdown' => $this->when($request->include_breakdown, function () {
                // This requires CalculationService
                $service = app(\App\Services\RAB\CalculationService::class);
                return $service->getAhspBreakdown($this->id);
            }),
            
            // Usage in BOQ (when requested)
            'usage' => $this->when($request->include_usage, function () {
                return [
                    'used_in_boq_items' => $this->boqItems()->count(),
                    'total_volume' => (float) $this->boqItems()->sum('volume'),
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
        $data = [
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

        // Include price calculation if project is loaded
        if ($this->relationLoaded('project')) {
            $projectItemPrice = \App\Models\RAB\ProjectItemPrice::where('project_id', $this->project_id)
                ->where('item_id', $ahspItem->item_id)
                ->first();

            if ($projectItemPrice) {
                $unitPrice = (float) $projectItemPrice->price;
                $total = $unitPrice * (float) $ahspItem->coefficient;

                $data['price'] = [
                    'unit_price' => $unitPrice,
                    'total_price' => $total,
                    'formatted' => [
                        'unit_price' => 'Rp ' . number_format($unitPrice, 0, ',', '.'),
                        'total_price' => 'Rp ' . number_format($total, 0, ',', '.'),
                    ],
                ];
            }
        }

        return $data;
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
                'resource_type' => 'project_ahsp',
            ],
        ];
    }
}
