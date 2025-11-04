<?php

namespace App\Services\RAB;

use App\Models\RAB\AhspSource;
use Illuminate\Support\Facades\DB;
use Exception;

class AhspSourceService
{
    /**
     * Get all AHSP sources with filters
     */
    public function getAll(array $filters = [])
    {
        $query = AhspSource::query();

        // Filter by active status
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        // Filter by user (multi-tenant)
        if (isset($filters['user_id'])) {
            $query->byUser($filters['user_id']);
        }

        // Search by name or code
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        return $query->active()->ordered()->get();
    }

    /**
     * Get AHSP source by ID
     */
    public function getById($id)
    {
        return AhspSource::with(['masterAhsp', 'projects'])->findOrFail($id);
    }

    /**
     * Create new AHSP source
     */
    public function create(array $data)
    {
        DB::beginTransaction();

        try {
            $data['created_by'] = $data['created_by'] ?? auth()->id();

            $ahspSource = AhspSource::create($data);

            DB::commit();

            return $ahspSource->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update AHSP source
     */
    public function update($id, array $data)
    {
        DB::beginTransaction();

        try {
            $ahspSource = AhspSource::findOrFail($id);
            
            $ahspSource->update($data);

            DB::commit();

            return $ahspSource->fresh();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete AHSP source (soft delete)
     */
    public function delete($id)
    {
        DB::beginTransaction();

        try {
            $ahspSource = AhspSource::findOrFail($id);

            // Check if source is being used
            $usageCount = $ahspSource->masterAhsp()->count() 
                + $ahspSource->projects()->count()
                + $ahspSource->projectTemplates()->count();

            if ($usageCount > 0) {
                throw new Exception("AHSP Source sedang digunakan dan tidak dapat dihapus. Silakan nonaktifkan saja.");
            }

            $ahspSource->delete();

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Toggle active status
     */
    public function toggleActive($id)
    {
        $ahspSource = AhspSource::findOrFail($id);
        $ahspSource->is_active = !$ahspSource->is_active;
        $ahspSource->save();

        return $ahspSource;
    }

    /**
     * Get usage statistics for a source
     */
    public function getUsageStats($id)
    {
        $ahspSource = AhspSource::findOrFail($id);

        return [
            'total_master_ahsp' => $ahspSource->masterAhsp()->count(),
            'total_projects' => $ahspSource->projects()->count(),
            'total_templates' => $ahspSource->projectTemplates()->count(),
            'total_project_ahsp' => $ahspSource->projectAhsp()->count(),
        ];
    }

    /**
     * Check if source code is unique
     */
    public function isCodeUnique($code, $excludeId = null)
    {
        $query = AhspSource::where('code', $code);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->count() === 0;
    }
}
