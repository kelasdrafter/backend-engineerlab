<?php

namespace App\Services\RAB;

use App\Models\RAB\Project;
use App\Models\RAB\ProjectCategory;
use App\Models\RAB\ProjectItemPrice;
use App\Models\RAB\ItemPrice;
use Illuminate\Support\Facades\DB;
use Exception;

class ProjectService
{
    /**
     * Get all projects with filters
     */
    public function getAll(array $filters = [])
    {
        $query = Project::with(['region', 'ahspSource', 'owner', 'template']);

        // Filter by owner (multi-tenant)
        if (isset($filters['user_id'])) {
            $query->byOwner($filters['user_id']);
        }

        // Filter by status
        if (isset($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        // Filter by AHSP source
        if (isset($filters['ahsp_source_id'])) {
            $query->bySource($filters['ahsp_source_id']);
        }

        // Filter by region
        if (isset($filters['region_id'])) {
            $query->byRegion($filters['region_id']);
        }

        // Filter by active
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        // Search by name
        if (isset($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        return $query->latest()->get();
    }

    /**
     * Get project by ID with relationships
     */
    public function getById($id)
    {
        return Project::with([
            'region',
            'ahspSource',
            'template',
            'owner',
            'rootCategories.children.boqItems',
        ])->findOrFail($id);
    }

    /**
     * Create new project
     */
    public function create(array $data)
    {
        DB::beginTransaction();

        try {
            $data['created_by'] = $data['created_by'] ?? auth()->id();

            // If from template, inherit AHSP source
            if (isset($data['template_id'])) {
                $template = \App\Models\RAB\ProjectTemplate::findOrFail($data['template_id']);
                $data['ahsp_source_id'] = $template->ahsp_source_id;
                $data['region_id'] = $template->region_id;
            }

            // Create project
            $project = Project::create($data);

            // Copy item prices from region to project
            $this->copyItemPrices($project);

            // If from template, copy structure
            if (isset($data['template_id'])) {
                $this->copyFromTemplate($project, $data['template_id']);
            }

            DB::commit();

            return $project->fresh(['region', 'ahspSource', 'rootCategories']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update project
     */
    public function update($id, array $data)
    {
        DB::beginTransaction();

        try {
            $project = Project::findOrFail($id);

            // AHSP source cannot be changed once set
            if (isset($data['ahsp_source_id']) && $data['ahsp_source_id'] != $project->ahsp_source_id) {
                throw new Exception("AHSP Source tidak dapat diubah setelah project dibuat.");
            }

            $project->update($data);

            DB::commit();

            return $project->fresh(['region', 'ahspSource']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete project
     */
    public function delete($id)
    {
        DB::beginTransaction();

        try {
            $project = Project::findOrFail($id);

            // Soft delete will cascade to categories and BOQ items
            $project->delete();

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Copy item prices from region to project
     */
    protected function copyItemPrices(Project $project)
    {
        $itemPrices = ItemPrice::where('region_id', $project->region_id)
            ->where('is_active', true)
            ->current()
            ->get();

        foreach ($itemPrices as $itemPrice) {
            ProjectItemPrice::create([
                'project_id' => $project->id,
                'item_id' => $itemPrice->item_id,
                'price' => $itemPrice->price,
                'source_type' => 'system',
                'source_reference' => $itemPrice->source,
            ]);
        }
    }

    /**
     * Copy structure from template
     */
    protected function copyFromTemplate(Project $project, $templateId)
    {
        $template = \App\Models\RAB\ProjectTemplate::with([
            'rootCategories.children.items.masterAhsp'
        ])->findOrFail($templateId);

        foreach ($template->rootCategories as $templateCategory) {
            $this->copyCategory($project->id, $templateCategory, null);
        }
    }

    /**
     * Recursively copy category and its children
     */
    protected function copyCategory($projectId, $templateCategory, $parentId)
    {
        $projectCategory = ProjectCategory::create([
            'project_id' => $projectId,
            'parent_id' => $parentId,
            'name' => $templateCategory->name,
            'code' => $templateCategory->code,
            'sort_order' => $templateCategory->sort_order,
        ]);

        // Copy items
        foreach ($templateCategory->items as $templateItem) {
            if ($templateItem->isAhsp()) {
                // Will be handled by ProjectAhspService
                // Just create placeholder for now
            }
        }

        // Recursively copy children
        foreach ($templateCategory->children as $childCategory) {
            $this->copyCategory($projectId, $childCategory, $projectCategory->id);
        }

        return $projectCategory;
    }

    /**
     * Update project status
     */
    public function updateStatus($id, $status)
    {
        $project = Project::findOrFail($id);
        $project->status = $status;
        $project->save();

        return $project;
    }

    /**
     * Get project summary with calculations
     */
    public function getSummary($id)
    {
        $project = Project::with(['rootCategories.boqItems'])->findOrFail($id);

        return [
            'project' => $project,
            'calculations' => [
                'boq_total' => $project->calculateTotalBoq(),
                'overhead' => $project->calculateOverhead(),
                'profit' => $project->calculateProfit(),
                'subtotal' => $project->calculateSubtotal(),
                'ppn' => $project->calculatePpn(),
                'grand_total' => $project->calculateGrandTotal(),
            ],
            'percentages' => [
                'overhead_percentage' => $project->overhead_percentage,
                'profit_percentage' => $project->profit_percentage,
                'ppn_percentage' => $project->ppn_percentage,
            ],
        ];
    }

    /**
     * Recalculate all BOQ prices
     */
    public function recalculateAllPrices($id)
    {
        DB::beginTransaction();

        try {
            $project = Project::with(['categories.boqItems.projectAhsp'])->findOrFail($id);

            foreach ($project->categories as $category) {
                foreach ($category->boqItems as $boqItem) {
                    if ($boqItem->isAhsp() && $boqItem->projectAhsp) {
                        $boqItem->updateUnitPriceFromAhsp();
                        $boqItem->recalculateTotal();
                    }
                }
            }

            DB::commit();

            return $this->getSummary($id);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
