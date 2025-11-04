<?php

namespace App\Observers;

use App\Models\RAB\Project;
use App\Models\RAB\ProjectItemPrice;
use App\Models\RAB\ItemPrice;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProjectObserver
{
    /**
     * Handle the Project "creating" event.
     * This runs before the project is saved to database
     *
     * @param  \App\Models\RAB\Project  $project
     * @return void
     */
    public function creating(Project $project): void
    {
        // Set default values if not provided
        if (is_null($project->overhead_percentage)) {
            $project->overhead_percentage = config('rab.defaults.overhead_percentage', 10.00);
        }

        if (is_null($project->profit_percentage)) {
            $project->profit_percentage = config('rab.defaults.profit_percentage', 10.00);
        }

        if (is_null($project->ppn_percentage)) {
            $project->ppn_percentage = config('rab.defaults.ppn_percentage', 11.00);
        }

        if (is_null($project->status)) {
            $project->status = 'draft';
        }

        if (is_null($project->is_active)) {
            $project->is_active = true;
        }

        // Set created_by if not set
        if (is_null($project->created_by) && auth()->check()) {
            $project->created_by = auth()->id();
        }

        // Log project creation
        if (config('rab.audit.enabled', true)) {
            Log::info('Creating new project', [
                'project_name' => $project->name,
                'region_id' => $project->region_id,
                'ahsp_source_id' => $project->ahsp_source_id,
                'created_by' => $project->created_by,
            ]);
        }
    }

    /**
     * Handle the Project "created" event.
     * This runs after the project is saved to database
     *
     * @param  \App\Models\RAB\Project  $project
     * @return void
     */
    public function created(Project $project): void
    {
        // Copy item prices from region to project
        $this->copyItemPrices($project);

        // If from template, copy structure
        if ($project->template_id) {
            $this->copyFromTemplate($project);
        }

        // Log successful creation
        if (config('rab.audit.enabled', true)) {
            Log::info('Project created successfully', [
                'project_id' => $project->id,
                'project_name' => $project->name,
                'from_template' => $project->template_id ? true : false,
            ]);
        }

        // Send notification if enabled
        if (config('rab.features.notifications', true)) {
            // You can implement notification logic here
            // e.g., notify project owner, admin, etc.
        }
    }

    /**
     * Handle the Project "updating" event.
     *
     * @param  \App\Models\RAB\Project  $project
     * @return void
     */
    public function updating(Project $project): void
    {
        // Prevent changing AHSP source after creation
        if ($project->isDirty('ahsp_source_id') && $project->getOriginal('ahsp_source_id')) {
            throw new \Exception('AHSP Source tidak dapat diubah setelah project dibuat.');
        }

        // Prevent changing region after creation
        if ($project->isDirty('region_id') && $project->getOriginal('region_id')) {
            throw new \Exception('Region tidak dapat diubah setelah project dibuat.');
        }

        // Log significant changes
        if (config('rab.audit.log_changes', true)) {
            $changes = $project->getDirty();
            
            if (!empty($changes)) {
                Log::info('Project being updated', [
                    'project_id' => $project->id,
                    'project_name' => $project->name,
                    'changes' => array_keys($changes),
                    'updated_by' => auth()->id(),
                ]);
            }
        }
    }

    /**
     * Handle the Project "updated" event.
     *
     * @param  \App\Models\RAB\Project  $project
     * @return void
     */
    public function updated(Project $project): void
    {
        // If financial percentages changed, recalculate BOQ
        if ($project->wasChanged(['overhead_percentage', 'profit_percentage', 'ppn_percentage'])) {
            // You can trigger recalculation here if needed
            if (config('rab.audit.enabled', true)) {
                Log::info('Project financial settings updated', [
                    'project_id' => $project->id,
                    'overhead_percentage' => $project->overhead_percentage,
                    'profit_percentage' => $project->profit_percentage,
                    'ppn_percentage' => $project->ppn_percentage,
                ]);
            }
        }

        // If status changed, log it
        if ($project->wasChanged('status')) {
            Log::info('Project status changed', [
                'project_id' => $project->id,
                'old_status' => $project->getOriginal('status'),
                'new_status' => $project->status,
                'changed_by' => auth()->id(),
            ]);
        }
    }

    /**
     * Handle the Project "deleting" event.
     *
     * @param  \App\Models\RAB\Project  $project
     * @return void
     */
    public function deleting(Project $project): void
    {
        // Log project deletion
        if (config('rab.audit.enabled', true)) {
            Log::warning('Project being deleted', [
                'project_id' => $project->id,
                'project_name' => $project->name,
                'deleted_by' => auth()->id(),
                'has_categories' => $project->categories()->count(),
                'has_boq_items' => DB::table('project_boq_items')
                    ->whereIn('project_category_id', $project->categories()->pluck('id'))
                    ->count(),
            ]);
        }

        // Note: Cascade deletes will be handled by database foreign keys
        // or by Laravel's cascadeOnDelete() in migrations
    }

    /**
     * Handle the Project "deleted" event.
     *
     * @param  \App\Models\RAB\Project  $project
     * @return void
     */
    public function deleted(Project $project): void
    {
        if (config('rab.audit.enabled', true)) {
            Log::warning('Project deleted successfully', [
                'project_id' => $project->id,
                'project_name' => $project->name,
            ]);
        }
    }

    /**
     * Handle the Project "restored" event.
     *
     * @param  \App\Models\RAB\Project  $project
     * @return void
     */
    public function restored(Project $project): void
    {
        if (config('rab.audit.enabled', true)) {
            Log::info('Project restored', [
                'project_id' => $project->id,
                'project_name' => $project->name,
                'restored_by' => auth()->id(),
            ]);
        }
    }

    /**
     * Handle the Project "force deleted" event.
     *
     * @param  \App\Models\RAB\Project  $project
     * @return void
     */
    public function forceDeleted(Project $project): void
    {
        if (config('rab.audit.enabled', true)) {
            Log::warning('Project permanently deleted', [
                'project_id' => $project->id,
                'project_name' => $project->name,
                'force_deleted_by' => auth()->id(),
            ]);
        }
    }

    /**
     * Copy item prices from region to project
     *
     * @param  \App\Models\RAB\Project  $project
     * @return void
     */
    protected function copyItemPrices(Project $project): void
    {
        try {
            $itemPrices = ItemPrice::where('region_id', $project->region_id)
                ->where('is_active', true)
                ->where('effective_date', '<=', now())
                ->where(function ($query) {
                    $query->whereNull('expired_date')
                        ->orWhere('expired_date', '>=', now());
                })
                ->get();

            foreach ($itemPrices as $itemPrice) {
                ProjectItemPrice::updateOrCreate(
                    [
                        'project_id' => $project->id,
                        'item_id' => $itemPrice->item_id,
                    ],
                    [
                        'price' => $itemPrice->price,
                        'source_type' => 'system',
                        'source_reference' => $itemPrice->source,
                    ]
                );
            }

            Log::info('Item prices copied to project', [
                'project_id' => $project->id,
                'total_prices' => $itemPrices->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to copy item prices to project', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Copy structure from template
     *
     * @param  \App\Models\RAB\Project  $project
     * @return void
     */
    protected function copyFromTemplate(Project $project): void
    {
        try {
            $template = \App\Models\RAB\ProjectTemplate::with([
                'rootCategories.children.items.masterAhsp'
            ])->find($project->template_id);

            if (!$template) {
                Log::warning('Template not found for project', [
                    'project_id' => $project->id,
                    'template_id' => $project->template_id,
                ]);
                return;
            }

            // Copy categories structure
            foreach ($template->rootCategories as $templateCategory) {
                $this->copyCategory($project->id, $templateCategory, null);
            }

            Log::info('Template structure copied to project', [
                'project_id' => $project->id,
                'template_id' => $template->id,
                'total_categories' => $template->categories()->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to copy template structure', [
                'project_id' => $project->id,
                'template_id' => $project->template_id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Recursively copy category and its children
     *
     * @param  int  $projectId
     * @param  \App\Models\RAB\ProjectTemplateCategory  $templateCategory
     * @param  int|null  $parentId
     * @return \App\Models\RAB\ProjectCategory
     */
    protected function copyCategory($projectId, $templateCategory, $parentId)
    {
        $projectCategory = \App\Models\RAB\ProjectCategory::create([
            'project_id' => $projectId,
            'parent_id' => $parentId,
            'name' => $templateCategory->name,
            'code' => $templateCategory->code,
            'sort_order' => $templateCategory->sort_order,
        ]);

        // Recursively copy children
        foreach ($templateCategory->children as $childCategory) {
            $this->copyCategory($projectId, $childCategory, $projectCategory->id);
        }

        return $projectCategory;
    }
}