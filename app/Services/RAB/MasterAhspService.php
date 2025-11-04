<?php

namespace App\Services\RAB;

use App\Models\RAB\MasterAhsp;
use App\Models\RAB\MasterAhspItem;
use Illuminate\Support\Facades\DB;
use Exception;

class MasterAhspService
{
    /**
     * Get all master AHSP with filters
     */
    public function getAll(array $filters = [])
    {
        $query = MasterAhsp::with(['ahspSource', 'creator']);

        // Filter by AHSP source
        if (isset($filters['ahsp_source_id'])) {
            $query->bySource($filters['ahsp_source_id']);
        }

        // Filter by source code
        if (isset($filters['source_code'])) {
            $query->bySourceCode($filters['source_code']);
        }

        // Filter by active status
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        // Filter by user (multi-tenant)
        if (isset($filters['user_id'])) {
            $query->byUser($filters['user_id']);
        }

        // Search by code or name
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        return $query->active()->get();
    }

    /**
     * Get AHSP by ID with composition
     */
    public function getById($id)
    {
        return MasterAhsp::with([
            'ahspSource',
            'items.item',
            'creator'
        ])->findOrFail($id);
    }

    /**
     * Create new master AHSP with composition
     */
    public function create(array $data)
    {
        DB::beginTransaction();

        try {
            $data['created_by'] = $data['created_by'] ?? auth()->id();

            // Create master AHSP
            $masterAhsp = MasterAhsp::create([
                'ahsp_source_id' => $data['ahsp_source_id'],
                'code' => $data['code'],
                'name' => $data['name'],
                'unit' => $data['unit'],
                'description' => $data['description'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'created_by' => $data['created_by'],
            ]);

            // Create composition items
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $index => $item) {
                    MasterAhspItem::create([
                        'master_ahsp_id' => $masterAhsp->id,
                        'category' => $item['category'],
                        'item_id' => $item['item_id'],
                        'coefficient' => $item['coefficient'],
                        'sort_order' => $index,
                    ]);
                }
            }

            DB::commit();

            return $masterAhsp->fresh(['items.item', 'ahspSource']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update master AHSP
     */
    public function update($id, array $data)
    {
        DB::beginTransaction();

        try {
            $masterAhsp = MasterAhsp::findOrFail($id);

            // Update master AHSP
            $masterAhsp->update([
                'ahsp_source_id' => $data['ahsp_source_id'] ?? $masterAhsp->ahsp_source_id,
                'code' => $data['code'] ?? $masterAhsp->code,
                'name' => $data['name'] ?? $masterAhsp->name,
                'unit' => $data['unit'] ?? $masterAhsp->unit,
                'description' => $data['description'] ?? $masterAhsp->description,
                'is_active' => $data['is_active'] ?? $masterAhsp->is_active,
            ]);

            // Update composition items if provided
            if (isset($data['items']) && is_array($data['items'])) {
                // Delete existing items
                $masterAhsp->items()->delete();

                // Create new items
                foreach ($data['items'] as $index => $item) {
                    MasterAhspItem::create([
                        'master_ahsp_id' => $masterAhsp->id,
                        'category' => $item['category'],
                        'item_id' => $item['item_id'],
                        'coefficient' => $item['coefficient'],
                        'sort_order' => $index,
                    ]);
                }
            }

            DB::commit();

            return $masterAhsp->fresh(['items.item', 'ahspSource']);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete master AHSP
     */
    public function delete($id)
    {
        DB::beginTransaction();

        try {
            $masterAhsp = MasterAhsp::findOrFail($id);

            // Check if AHSP is being used in projects
            $usageCount = $masterAhsp->projectAhsp()->count() 
                + $masterAhsp->templateItems()->count();

            if ($usageCount > 0) {
                throw new Exception("Master AHSP sedang digunakan di project atau template dan tidak dapat dihapus.");
            }

            // Delete composition items first
            $masterAhsp->items()->delete();

            // Delete master AHSP
            $masterAhsp->delete();

            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Calculate unit price for specific region
     */
    public function calculateUnitPrice($id, $regionId)
    {
        $masterAhsp = MasterAhsp::with('items.item')->findOrFail($id);

        return $masterAhsp->calculateUnitPrice($regionId);
    }

    /**
     * Get composition breakdown with prices
     */
    public function getCompositionBreakdown($id, $regionId)
    {
        $masterAhsp = MasterAhsp::with('items.item')->findOrFail($id);

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

        foreach ($masterAhsp->items as $ahspItem) {
            $itemPrice = $ahspItem->item->getPriceForRegion($regionId);
            $price = $itemPrice ? $itemPrice->price : 0;
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
 * Duplicate master AHSP
 */
public function duplicate($id, array $newData = [])
{
    DB::beginTransaction();

    try {
        $original = MasterAhsp::with('items')->findOrFail($id);

        // Generate unique code jika tidak disediakan
        if (!isset($newData['code'])) {
            $sourceId = $newData['ahsp_source_id'] ?? $original->ahsp_source_id;
            $baseCode = $original->code;
            $counter = 1;
            
            do {
                $newCode = $baseCode . '-COPY' . ($counter > 1 ? '-' . $counter : '');
                $counter++;
            } while (!$this->isCodeUniqueInSource($newCode, $sourceId));
            
            $newData['code'] = $newCode;
        }

        // Generate unique name jika tidak disediakan
        if (!isset($newData['name'])) {
            $newData['name'] = $original->name . ' (Copy)';
        }

        // Create duplicate
        $duplicate = MasterAhsp::create([
            'ahsp_source_id' => $newData['ahsp_source_id'] ?? $original->ahsp_source_id,
            'code' => $newData['code'],
            'name' => $newData['name'],
            'unit' => $original->unit,
            'description' => $original->description,
            'is_active' => $newData['is_active'] ?? true,
            'created_by' => auth()->id(),
        ]);

        // Duplicate composition items
        foreach ($original->items as $item) {
            MasterAhspItem::create([
                'master_ahsp_id' => $duplicate->id,
                'category' => $item->category,
                'item_id' => $item->item_id,
                'coefficient' => $item->coefficient,
                'sort_order' => $item->sort_order,
            ]);
        }

        DB::commit();

        return $duplicate->fresh(['items.item', 'ahspSource']);
    } catch (Exception $e) {
        DB::rollBack();
        throw $e;
    }
}

    /**
     * Check if code is unique within source
     */
    public function isCodeUniqueInSource($code, $sourceId, $excludeId = null)
    {
        $query = MasterAhsp::where('code', $code)
            ->where('ahsp_source_id', $sourceId);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->count() === 0;
    }
}
