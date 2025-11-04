<?php

namespace App\Http\Resources\RAB;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
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
            'description' => $this->description,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'status_color' => $this->status_color,
            'is_active' => $this->is_active,
            
            // Financial settings
            'financial' => [
                'overhead_percentage' => (float) $this->overhead_percentage,
                'profit_percentage' => (float) $this->profit_percentage,
                'ppn_percentage' => (float) $this->ppn_percentage,
            ],
            
            // Dates
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'duration_days' => $this->when(
                $this->start_date && $this->end_date,
                fn() => $this->start_date->diffInDays($this->end_date)
            ),
            
            // Region
            'region' => $this->whenLoaded('region', function () {
                return [
                    'id' => $this->region->id,
                    'code' => $this->region->code,
                    'name' => $this->region->name,
                    'full_name' => $this->region->full_name,
                    'province' => $this->region->province,
                    'city' => $this->region->city,
                    'type' => $this->region->type,
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
            
            // Template (if created from template)
            'template' => $this->whenLoaded('template', function () {
                if (!$this->template) {
                    return null;
                }
                return [
                    'id' => $this->template->id,
                    'name' => $this->template->name,
                    'is_global' => $this->template->is_global,
                ];
            }),
            
            // Is from template flag
            'is_from_template' => $this->isFromTemplate(),
            
            // Owner
            'owner' => $this->whenLoaded('owner', function () {
                return [
                    'id' => $this->owner->id,
                    'name' => $this->owner->name,
                    'email' => $this->owner->email,
                ];
            }),
            
            // BOQ Categories structure
            'categories' => $this->whenLoaded('rootCategories', function () {
                return $this->rootCategories->map(function ($category) {
                    return $this->formatCategory($category);
                });
            }),
            
            // Calculations (when requested)
            'calculations' => $this->when($request->include_calculations, function () {
                return [
                    'boq_total' => (float) $this->calculateTotalBoq(),
                    'overhead' => (float) $this->calculateOverhead(),
                    'profit' => (float) $this->calculateProfit(),
                    'subtotal' => (float) $this->calculateSubtotal(),
                    'ppn' => (float) $this->calculatePpn(),
                    'grand_total' => (float) $this->calculateGrandTotal(),
                    'formatted' => [
                        'boq_total' => 'Rp ' . number_format($this->calculateTotalBoq(), 0, ',', '.'),
                        'overhead' => 'Rp ' . number_format($this->calculateOverhead(), 0, ',', '.'),
                        'profit' => 'Rp ' . number_format($this->calculateProfit(), 0, ',', '.'),
                        'subtotal' => 'Rp ' . number_format($this->calculateSubtotal(), 0, ',', '.'),
                        'ppn' => 'Rp ' . number_format($this->calculatePpn(), 0, ',', '.'),
                        'grand_total' => 'Rp ' . number_format($this->calculateGrandTotal(), 0, ',', '.'),
                    ],
                ];
            }),
            
            // Statistics (when requested)
            'statistics' => $this->when($request->include_statistics, function () {
                return [
                    'total_categories' => $this->categories()->count(),
                    'total_boq_items' => \App\Models\RAB\ProjectBoqItem::whereHas('projectCategory', function ($q) {
                        $q->where('project_id', $this->id);
                    })->count(),
                    'total_ahsp_used' => $this->projectAhsp()->count(),
                    'total_custom_ahsp' => $this->projectAhsp()->custom()->count(),
                    'total_master_ahsp' => $this->projectAhsp()->fromMaster()->count(),
                ];
            }),
            
            // Timestamps
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'deleted_at' => $this->when($this->deleted_at, $this->deleted_at?->toIso8601String()),
        ];
    }

    /**
     * Format category with children recursively
     */
    protected function formatCategory($category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'code' => $category->code,
            'sort_order' => $category->sort_order,
            'is_root' => $category->isRoot(),
            'full_path' => $category->full_path,
            
            // BOQ Items in this category
            'boq_items' => $category->relationLoaded('boqItems') 
                ? $category->boqItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'item_type' => $item->item_type,
                        'code' => $item->code,
                        'name' => $item->name,
                        'unit' => $item->unit,
                        'volume' => (float) $item->volume,
                        'unit_price' => (float) $item->unit_price,
                        'total_price' => (float) $item->total_price,
                        'formatted' => [
                            'volume' => $item->formatted_volume,
                            'unit_price' => $item->formatted_unit_price,
                            'total_price' => $item->formatted_total_price,
                        ],
                    ];
                })
                : [],
            
            // Child categories (recursive)
            'children' => $category->relationLoaded('children')
                ? $category->children->map(function ($child) {
                    return $this->formatCategory($child);
                })
                : [],
            
            // Category total
            'category_total' => $category->relationLoaded('boqItems') 
                ? (float) $category->calculateTotal()
                : null,
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
                'resource_type' => 'project',
            ],
        ];
    }
}
