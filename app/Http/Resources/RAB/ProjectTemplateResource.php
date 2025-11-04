<?php

namespace App\Http\Resources\RAB;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectTemplateResource extends JsonResource
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
            'is_global' => $this->is_global,
            'is_active' => $this->is_active,
            
            // Access info
            'access' => [
                'is_global' => $this->is_global,
                'can_be_used_by_current_user' => $this->when(
                    auth()->check(),
                    fn() => $this->canBeUsedBy(auth()->id())
                ),
            ],
            
            // Region
            'region' => $this->whenLoaded('region', function () {
                return [
                    'id' => $this->region->id,
                    'code' => $this->region->code,
                    'name' => $this->region->name,
                    'full_name' => $this->region->full_name,
                    'province' => $this->region->province,
                    'city' => $this->region->city,
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
            
            // Creator
            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->name,
                ];
            }),
            
            // Template structure (categories)
            'categories' => $this->whenLoaded('rootCategories', function () {
                return $this->rootCategories->map(function ($category) {
                    return $this->formatTemplateCategory($category);
                });
            }),
            
            // Statistics (when requested)
            'statistics' => $this->when($request->include_statistics, function () {
                return [
                    'total_categories' => $this->categories()->count(),
                    'total_root_categories' => $this->rootCategories()->count(),
                    'total_items' => \App\Models\RAB\ProjectTemplateItem::whereHas('templateCategory', function ($q) {
                        $q->where('template_id', $this->id);
                    })->count(),
                    'total_projects_created' => $this->projects()->count(),
                ];
            }),
            
            // Usage info (when requested)
            'usage' => $this->when($request->include_usage, function () {
                return [
                    'used_in_projects' => $this->projects()->count(),
                    'recent_projects' => $this->projects()->latest()->take(5)->get()->map(function ($project) {
                        return [
                            'id' => $project->id,
                            'name' => $project->name,
                            'created_at' => $project->created_at->toIso8601String(),
                        ];
                    }),
                ];
            }),
            
            // Timestamps
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'deleted_at' => $this->when($this->deleted_at, $this->deleted_at?->toIso8601String()),
        ];
    }

    /**
     * Format template category with children and items
     */
    protected function formatTemplateCategory($category): array
    {
        return [
            'id' => $category->id,
            'name' => $category->name,
            'code' => $category->code,
            'sort_order' => $category->sort_order,
            'is_root' => $category->isRoot(),
            'full_path' => $category->full_path,
            
            // Template items in this category
            'items' => $category->relationLoaded('items')
                ? $category->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'item_type' => $item->item_type,
                        'code' => $item->item_code,
                        'name' => $item->item_name,
                        'unit' => $item->item_unit,
                        'default_volume' => (float) $item->default_volume,
                        'sort_order' => $item->sort_order,
                        
                        // Master AHSP reference (if type = ahsp)
                        'master_ahsp' => $item->relationLoaded('masterAhsp') && $item->masterAhsp
                            ? [
                                'id' => $item->masterAhsp->id,
                                'code' => $item->masterAhsp->code,
                                'name' => $item->masterAhsp->name,
                                'unit' => $item->masterAhsp->unit,
                            ]
                            : null,
                    ];
                })
                : [],
            
            // Child categories (recursive)
            'children' => $category->relationLoaded('children')
                ? $category->children->map(function ($child) {
                    return $this->formatTemplateCategory($child);
                })
                : [],
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
                'resource_type' => 'project_template',
            ],
        ];
    }
}
