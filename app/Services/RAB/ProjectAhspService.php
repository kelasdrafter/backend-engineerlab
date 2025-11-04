<?php

namespace App\Services\RAB;

use App\Models\RAB\ProjectAhsp;
use App\Models\RAB\ProjectAhspItem;
use App\Models\RAB\MasterAhsp;
use Illuminate\Support\Facades\DB;
use Exception;

class ProjectAhspService
{
    /**
     * Get all project AHSP with filters
     */
    public function getByProject($projectId, array $filters = [])
    {
        $query = ProjectAhsp::with(['ahspSource', 'masterAhsp', 'items.item'])
            ->byProject($projectId);

        // Filter by source type
        if (isset($filters['source_type'])) {
            if ($filters['source_type'] === 'master') {
                $query->fromMaster();
            } elseif ($filters['source_type'] === 'custom') {
                $query->custom();
            }
        }

        // Search by code or name
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        return $query->get();
    }

    /**
     * Get project AHSP by ID
     */
    public function getById($id)
    {
        return ProjectAhsp::with([
            'project',
            'ahspSource',
            'masterAhsp',
            'items.item',
        ])->findOrFail($id);
    }

    /**
     * Add AHSP from master to project
     */
    public function addFromMaster($projectId, $masterAhspId)
    {
        DB::beginTransaction();

        try {
            $project = \App\Models\RAB\Project::findOrFail($projectId);
            $masterAhsp = MasterAhsp::with('items')->findOrFail($masterAhspId);

            // Validate: AHSP source must match project source
            if ($masterAhsp->ahsp_source_id != $project->ahsp_source_id) {
                throw new Exception("AHSP Source tidak sesuai dengan project. Project menggunakan source: " . $project->ahspSource->name);
            }

            // Check if already added
            $existing = ProjectAhsp::where('project_id', $projectId)
                ->where('master_ahsp_id', $masterAhspId)
                ->first();

            if ($existing) {
                throw new Exception("AHSP ini sudah ditambahkan ke project.");
            }

            // Create project AHSP
            $projectAhsp = ProjectAhsp::create([
                'project_id' => $projectId,
                'ahsp_source_id' => $masterAhsp->ahsp_source_id,
                'source_type' => 'master',
                'master_ahsp_id' => $masterAhsp->id,
                'code' => $masterAhsp->code,
                'name' => $masterAhsp->name,
                'unit' => $masterAhsp->unit,
                'description' => $masterAhsp->description,
            ]);

            // Copy composition items
            foreach ($masterAhsp->items as $item) {
                ProjectAhspItem::create([
                    'project_ahsp_id' => $projectAhsp->id,
                    'category' => $item->category,
                    'item_id' => $item->item_id,
                    'coefficient' => $item->coefficient,
                    'sort_order' => $item->sort_order,
                ]);
            }

            DB::commit();

            return $projectAhsp->fresh(['items.item', 'ahspSource']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Create custom AHSP for project
     */
    public function createCustom($projectId, array $data)
    {
        DB::beginTransaction();

        try {
            $project = \App\Models\RAB\Project::findOrFail($projectId);

            // Create custom project AHSP
            $projectAhsp = ProjectAhsp::create([
                'project_id' => $projectId,
                'ahsp_source_id' => $project->ahsp_source_id,
                'source_type' => 'custom',
                'master_ahsp_id' => null,
                'code' => $data['code'],
                'name' => $data['name'],
                'unit' => $data['unit'],
                'description' => $data['description'] ?? null,
            ]);

            // Create composition items
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $index => $item) {
                    ProjectAhspItem::create([
                        'project_ahsp_id' => $projectAhsp->id,
                        'category' => $item['category'],
                        'item_id' => $item['item_id'],
                        'coefficient' => $item['coefficient'],
                        'sort_order' => $index,
                    ]);
                }
            }

            DB::commit();

            return $projectAhsp->fresh(['items.item', 'ahspSource']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update project AHSP composition
     */
    public function updateComposition($id, array $items)
    {
        DB::beginTransaction();

        try {
            $projectAhsp = ProjectAhsp::findOrFail($id);

            // Only custom AHSP can be edited
            if ($projectAhsp->isFromMaster()) {
                throw new Exception("AHSP dari master tidak dapat diedit. Buat custom AHSP baru jika perlu modifikasi.");
            }

            // Delete existing items
            $projectAhsp->items()->delete();

            // Create new items
            foreach ($items as $index => $item) {
                ProjectAhspItem::create([
                    'project_ahsp_id' => $projectAhsp->id,
                    'category' => $item['category'],
                    'item_id' => $item['item_id'],
                    'coefficient' => $item['coefficient'],
                    'sort_order' => $index,
                ]);
            }

            DB::commit();

            return $projectAhsp->fresh(['items.item']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete project AHSP
     */
    public function delete($id)
    {
        DB::beginTransaction();

        try {
            $projectAhsp = ProjectAhsp::findOrFail($id);

            // Check if being used in BOQ
            if ($projectAhsp->boqItems()->count() > 0) {
                throw new Exception("AHSP sedang digunakan di BOQ dan tidak dapat dihapus.");
            }

            // Delete composition items
            $projectAhsp->items()->delete();

            // Delete project AHSP
            $projectAhsp->delete();

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Calculate unit price
     */
    public function calculateUnitPrice($id)
    {
        $projectAhsp = ProjectAhsp::with('items.item')->findOrFail($id);

        return $projectAhsp->calculateUnitPrice();
    }

    /**
     * Get composition breakdown with prices
     */
    public function getCompositionBreakdown($id)
    {
        $projectAhsp = ProjectAhsp::with([
            'project',
            'items.item'
        ])->findOrFail($id);

        $breakdown = [
            'materials' => [],
            'labor' => [],
            'equipment' => [],
            'totals' => [
                'material_total' => 0,
                'labor_total' => 0,
                'equipment_total' => 0,
                'grand_total' => 0,
            ],
        ];

        foreach ($projectAhsp->items as $ahspItem) {
            $projectItemPrice = \App\Models\RAB\ProjectItemPrice::where('project_id', $projectAhsp->project_id)
                ->where('item_id', $ahspItem->item_id)
                ->first();

            $price = $projectItemPrice ? $projectItemPrice->price : 0;
            $total = $price * $ahspItem->coefficient;

            $itemData = [
                'item' => $ahspItem->item,
                'coefficient' => $ahspItem->coefficient,
                'unit_price' => $price,
                'total_price' => $total,
            ];

            switch ($ahspItem->category) {
                case 'material':
                    $breakdown['materials'][] = $itemData;
                    $breakdown['totals']['material_total'] += $total;
                    break;
                case 'labor':
                    $breakdown['labor'][] = $itemData;
                    $breakdown['totals']['labor_total'] += $total;
                    break;
                case 'equipment':
                    $breakdown['equipment'][] = $itemData;
                    $breakdown['totals']['equipment_total'] += $total;
                    break;
            }
        }

        $breakdown['totals']['grand_total'] = 
            $breakdown['totals']['material_total'] + 
            $breakdown['totals']['labor_total'] + 
            $breakdown['totals']['equipment_total'];

        return $breakdown;
    }

    /**
     * Sync composition from master AHSP
     */
    public function syncFromMaster($id)
    {
        DB::beginTransaction();

        try {
            $projectAhsp = ProjectAhsp::with('masterAhsp.items')->findOrFail($id);

            if (!$projectAhsp->isFromMaster()) {
                throw new Exception("Hanya AHSP dari master yang dapat di-sync.");
            }

            if (!$projectAhsp->masterAhsp) {
                throw new Exception("Master AHSP tidak ditemukan.");
            }

            // Delete existing composition
            $projectAhsp->items()->delete();

            // Copy from master
            foreach ($projectAhsp->masterAhsp->items as $item) {
                ProjectAhspItem::create([
                    'project_ahsp_id' => $projectAhsp->id,
                    'category' => $item->category,
                    'item_id' => $item->item_id,
                    'coefficient' => $item->coefficient,
                    'sort_order' => $item->sort_order,
                ]);
            }

            // Update AHSP details
            $projectAhsp->update([
                'code' => $projectAhsp->masterAhsp->code,
                'name' => $projectAhsp->masterAhsp->name,
                'unit' => $projectAhsp->masterAhsp->unit,
                'description' => $projectAhsp->masterAhsp->description,
            ]);

            DB::commit();

            return $projectAhsp->fresh(['items.item']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
