<?php

namespace App\Observers;

use App\Models\RAB\ProjectAhsp;
use App\Models\RAB\ProjectBoqItem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProjectAhspObserver
{
    /**
     * Handle the ProjectAhsp "creating" event.
     *
     * @param  \App\Models\RAB\ProjectAhsp  $projectAhsp
     * @return void
     */
    public function creating(ProjectAhsp $projectAhsp): void
    {
        // Validate AHSP source matches project source
        if ($projectAhsp->project && $projectAhsp->ahsp_source_id) {
            $project = $projectAhsp->project;
            
            if ($project->ahsp_source_id != $projectAhsp->ahsp_source_id) {
                throw new \Exception(
                    "AHSP Source tidak sesuai dengan project. Project menggunakan source: {$project->ahspSource->name}"
                );
            }
        }

        // Log creation
        if (config('rab.audit.enabled', true)) {
            Log::info('Creating project AHSP', [
                'project_id' => $projectAhsp->project_id,
                'code' => $projectAhsp->code,
                'name' => $projectAhsp->name,
                'source_type' => $projectAhsp->source_type,
                'master_ahsp_id' => $projectAhsp->master_ahsp_id,
            ]);
        }
    }

    /**
     * Handle the ProjectAhsp "created" event.
     *
     * @param  \App\Models\RAB\ProjectAhsp  $projectAhsp
     * @return void
     */
    public function created(ProjectAhsp $projectAhsp): void
    {
        if (config('rab.audit.enabled', true)) {
            Log::info('Project AHSP created successfully', [
                'id' => $projectAhsp->id,
                'project_id' => $projectAhsp->project_id,
                'code' => $projectAhsp->code,
                'source_type' => $projectAhsp->source_type,
                'total_items' => $projectAhsp->items()->count(),
            ]);
        }

        // Send notification if enabled
        if (config('rab.features.notifications', true)) {
            // You can implement notification logic here
        }
    }

    /**
     * Handle the ProjectAhsp "updating" event.
     *
     * @param  \App\Models\RAB\ProjectAhsp  $projectAhsp
     * @return void
     */
    public function updating(ProjectAhsp $projectAhsp): void
    {
        // Prevent changing source_type after creation
        if ($projectAhsp->isDirty('source_type') && $projectAhsp->getOriginal('source_type')) {
            throw new \Exception('Tipe sumber AHSP tidak dapat diubah setelah dibuat.');
        }

        // Prevent changing project after creation
        if ($projectAhsp->isDirty('project_id') && $projectAhsp->getOriginal('project_id')) {
            throw new \Exception('Project tidak dapat diubah setelah AHSP dibuat.');
        }

        // Prevent changing AHSP source after creation
        if ($projectAhsp->isDirty('ahsp_source_id') && $projectAhsp->getOriginal('ahsp_source_id')) {
            throw new \Exception('AHSP Source tidak dapat diubah setelah AHSP dibuat.');
        }

        // Prevent editing AHSP from master
        if ($projectAhsp->isFromMaster()) {
            $allowedFields = ['description']; // Only description can be updated
            $dirtyFields = array_keys($projectAhsp->getDirty());
            $disallowedChanges = array_diff($dirtyFields, $allowedFields);

            if (!empty($disallowedChanges)) {
                throw new \Exception(
                    'AHSP dari master tidak dapat diedit. Hanya deskripsi yang dapat diubah. ' .
                    'Buat custom AHSP baru jika perlu modifikasi.'
                );
            }
        }

        // Log changes
        if (config('rab.audit.log_changes', true)) {
            $changes = $projectAhsp->getDirty();
            
            if (!empty($changes)) {
                Log::info('Project AHSP being updated', [
                    'id' => $projectAhsp->id,
                    'project_id' => $projectAhsp->project_id,
                    'code' => $projectAhsp->code,
                    'changes' => array_keys($changes),
                    'updated_by' => auth()->id(),
                ]);
            }
        }
    }

    /**
     * Handle the ProjectAhsp "updated" event.
     *
     * @param  \App\Models\RAB\ProjectAhsp  $projectAhsp
     * @return void
     */
    public function updated(ProjectAhsp $projectAhsp): void
    {
        // If composition changed, recalculate unit price
        if ($projectAhsp->wasChanged(['code', 'name', 'unit'])) {
            // Trigger recalculation of BOQ items using this AHSP
            $this->recalculateBoqItems($projectAhsp);
        }

        if (config('rab.audit.enabled', true)) {
            Log::info('Project AHSP updated successfully', [
                'id' => $projectAhsp->id,
                'project_id' => $projectAhsp->project_id,
                'code' => $projectAhsp->code,
            ]);
        }
    }

    /**
     * Handle the ProjectAhsp "deleting" event.
     *
     * @param  \App\Models\RAB\ProjectAhsp  $projectAhsp
     * @return void
     */
    public function deleting(ProjectAhsp $projectAhsp): void
    {
        // Check if being used in BOQ
        $boqItemsCount = $projectAhsp->boqItems()->count();
        
        if ($boqItemsCount > 0) {
            throw new \Exception(
                "AHSP '{$projectAhsp->name}' sedang digunakan di {$boqItemsCount} item BOQ dan tidak dapat dihapus. " .
                "Hapus item BOQ terlebih dahulu."
            );
        }

        // Log deletion
        if (config('rab.audit.enabled', true)) {
            Log::warning('Project AHSP being deleted', [
                'id' => $projectAhsp->id,
                'project_id' => $projectAhsp->project_id,
                'code' => $projectAhsp->code,
                'name' => $projectAhsp->name,
                'source_type' => $projectAhsp->source_type,
                'deleted_by' => auth()->id(),
                'total_items' => $projectAhsp->items()->count(),
            ]);
        }

        // Delete composition items
        // Note: This will be handled automatically if cascadeOnDelete is set in migration
        // But we can explicitly do it here for logging purposes
        try {
            $itemsDeleted = $projectAhsp->items()->count();
            $projectAhsp->items()->delete();
            
            Log::info('Project AHSP composition items deleted', [
                'project_ahsp_id' => $projectAhsp->id,
                'items_deleted' => $itemsDeleted,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete project AHSP composition items', [
                'project_ahsp_id' => $projectAhsp->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the ProjectAhsp "deleted" event.
     *
     * @param  \App\Models\RAB\ProjectAhsp  $projectAhsp
     * @return void
     */
    public function deleted(ProjectAhsp $projectAhsp): void
    {
        if (config('rab.audit.enabled', true)) {
            Log::warning('Project AHSP deleted successfully', [
                'id' => $projectAhsp->id,
                'project_id' => $projectAhsp->project_id,
                'code' => $projectAhsp->code,
            ]);
        }
    }

    /**
     * Handle the ProjectAhsp "restored" event.
     *
     * @param  \App\Models\RAB\ProjectAhsp  $projectAhsp
     * @return void
     */
    public function restored(ProjectAhsp $projectAhsp): void
    {
        if (config('rab.audit.enabled', true)) {
            Log::info('Project AHSP restored', [
                'id' => $projectAhsp->id,
                'project_id' => $projectAhsp->project_id,
                'code' => $projectAhsp->code,
                'restored_by' => auth()->id(),
            ]);
        }
    }

    /**
     * Handle the ProjectAhsp "force deleted" event.
     *
     * @param  \App\Models\RAB\ProjectAhsp  $projectAhsp
     * @return void
     */
    public function forceDeleted(ProjectAhsp $projectAhsp): void
    {
        if (config('rab.audit.enabled', true)) {
            Log::warning('Project AHSP permanently deleted', [
                'id' => $projectAhsp->id,
                'project_id' => $projectAhsp->project_id,
                'code' => $projectAhsp->code,
                'force_deleted_by' => auth()->id(),
            ]);
        }
    }

    /**
     * Recalculate BOQ items that use this AHSP
     *
     * @param  \App\Models\RAB\ProjectAhsp  $projectAhsp
     * @return void
     */
    protected function recalculateBoqItems(ProjectAhsp $projectAhsp): void
    {
        try {
            $boqItems = $projectAhsp->boqItems()->get();

            if ($boqItems->isEmpty()) {
                return;
            }

            DB::beginTransaction();

            foreach ($boqItems as $boqItem) {
                // Recalculate unit price from AHSP
                $boqItem->updateUnitPriceFromAhsp();
                $boqItem->recalculateTotal();
            }

            DB::commit();

            Log::info('BOQ items recalculated after AHSP update', [
                'project_ahsp_id' => $projectAhsp->id,
                'total_boq_items' => $boqItems->count(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to recalculate BOQ items', [
                'project_ahsp_id' => $projectAhsp->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Sync composition from master AHSP when master is updated
     *
     * @param  \App\Models\RAB\ProjectAhsp  $projectAhsp
     * @return void
     */
    protected function syncFromMaster(ProjectAhsp $projectAhsp): void
    {
        if (!$projectAhsp->isFromMaster() || !$projectAhsp->masterAhsp) {
            return;
        }

        try {
            DB::beginTransaction();

            // Delete current composition
            $projectAhsp->items()->delete();

            // Copy from master
            $masterAhsp = $projectAhsp->masterAhsp->load('items');
            
            foreach ($masterAhsp->items as $item) {
                \App\Models\RAB\ProjectAhspItem::create([
                    'project_ahsp_id' => $projectAhsp->id,
                    'category' => $item->category,
                    'item_id' => $item->item_id,
                    'coefficient' => $item->coefficient,
                    'sort_order' => $item->sort_order,
                ]);
            }

            // Update AHSP details
            $projectAhsp->update([
                'code' => $masterAhsp->code,
                'name' => $masterAhsp->name,
                'unit' => $masterAhsp->unit,
                'description' => $masterAhsp->description,
            ]);

            DB::commit();

            Log::info('Project AHSP synced from master', [
                'project_ahsp_id' => $projectAhsp->id,
                'master_ahsp_id' => $masterAhsp->id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to sync project AHSP from master', [
                'project_ahsp_id' => $projectAhsp->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}