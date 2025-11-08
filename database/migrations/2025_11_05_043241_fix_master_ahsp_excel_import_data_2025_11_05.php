<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\RAB\MasterAhsp;
use App\Models\RAB\MasterAhspItem;
use App\Models\RAB\Item;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Fix Master AHSP Excel import data inconsistencies
     * 
     * Issue: Excel upload created wrong category mapping and missing items
     * - Materials went to wrong categories
     * - Only 2/8 items were saved correctly
     * - Coefficient values were incorrect
     * 
     * Created by: kelasdrafter
     */
    public function up(): void
    {
        $startTime = Carbon::now()->toISOString();
        
        Log::info('🔧 kelasdrafter - Starting Master AHSP Excel data fix', [
            'started_at' => $startTime,
            'migration_file' => '2025_11_05_043506_fix_master_ahsp_excel_import_data',
            'issue' => 'Excel import category mapping and missing items'
        ]);

        // Find Master AHSP dengan kode 2.2.1.57 (dari Excel upload yang bermasalah)
        $masterAhsp = MasterAhsp::where('code', '2.2.1.57')->first();
        
        if (!$masterAhsp) {
            Log::warning('Master AHSP with code 2.2.1.57 not found, skipping fix', [
                'searched_code' => '2.2.1.57',
                'available_codes' => MasterAhsp::pluck('code')->toArray()
            ]);
            
            echo "⚠️  Master AHSP with code 2.2.1.57 not found!\n";
            echo "Available codes: " . MasterAhsp::pluck('code')->implode(', ') . "\n";
            return;
        }

        Log::info("Found Master AHSP to fix", [
            'id' => $masterAhsp->id,
            'code' => $masterAhsp->code,
            'name' => $masterAhsp->name,
            'unit' => $masterAhsp->unit
        ]);

        // Backup current wrong data untuk audit trail
        $currentItems = $masterAhsp->items()->with('item')->get();
        $backupData = $currentItems->map(function($item) {
            return [
                'id' => $item->id,
                'category' => $item->category,
                'item_id' => $item->item_id,
                'item_name' => $item->item->name,
                'item_type' => $item->item->type,
                'item_code' => $item->item->code,
                'coefficient' => $item->coefficient,
                'sort_order' => $item->sort_order,
                'created_at' => $item->created_at?->toISOString(),
            ];
        })->toArray();

        Log::info('Backing up current wrong data before fix', [
            'master_ahsp_id' => $masterAhsp->id,
            'total_current_items' => $currentItems->count(),
            'backup_data' => $backupData
        ]);

        echo "📋 Current items before fix:\n";
        foreach ($currentItems as $item) {
            echo "   - {$item->category}: {$item->item->name} ({$item->item->type}) - {$item->coefficient}\n";
        }

        // Store backup in migration_logs table (optional - create if needed)
        try {
            DB::table('migration_logs')->insert([
                'migration' => '2025_11_05_043506_fix_master_ahsp_excel_import_data',
                'action' => 'backup_before_fix',
                'data' => json_encode([
                    'master_ahsp_id' => $masterAhsp->id,
                    'backup_data' => $backupData
                ]),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        } catch (\Exception $e) {
            Log::info('Migration logs table not available, skipping backup storage');
        }

        // Delete wrong items
        $deletedCount = $masterAhsp->items()->delete();
        Log::info("Deleted wrong items", ['count' => $deletedCount]);
        echo "🗑️  Deleted {$deletedCount} wrong items\n";

        // Correct items sesuai Excel upload yang seharusnya
        $correctItemsFromExcel = [
            // A. TENAGA KERJA (dari Excel section A)
            ['item_name' => 'Pekerja', 'expected_type' => 'labor', 'coefficient' => 0.70, 'excel_code' => 'L.01.01'],
            ['item_name' => 'Tukang Batu', 'expected_type' => 'labor', 'coefficient' => 0.35, 'excel_code' => 'L.02.03'],
            ['item_name' => 'Kepala Tukang', 'expected_type' => 'labor', 'coefficient' => 0.04, 'excel_code' => 'L.03.01'],
            ['item_name' => 'Mandor', 'expected_type' => 'labor', 'coefficient' => 0.04, 'excel_code' => 'L.04.01'],
            
            // B. BAHAN (dari Excel section B)
            ['item_name' => 'Keramik granit kw.1 ukuran 60 x 60 cm', 'expected_type' => 'material', 'coefficient' => 3.10, 'excel_code' => null],
            ['item_name' => 'Semen Portland', 'expected_type' => 'material', 'coefficient' => 9.60, 'excel_code' => null],
            ['item_name' => 'Pasir Pasang', 'expected_type' => 'material', 'coefficient' => 0.05, 'excel_code' => null], // Was wrong: 0.045
            ['item_name' => 'Semen Warna', 'expected_type' => 'material', 'coefficient' => 1.50, 'excel_code' => null],
        ];

        $createdCount = 0;
        $missingItems = [];
        $mismatchedTypes = [];
        $createdItems = [];

        echo "\n🔧 Creating correct items:\n";

        foreach ($correctItemsFromExcel as $index => $itemData) {
            // Try exact match first
            $item = Item::where('name', $itemData['item_name'])->first();
            
            // If not found, try partial match
            if (!$item) {
                $item = Item::where('name', 'LIKE', "%{$itemData['item_name']}%")->first();
            }
            
            if ($item) {
                // Validate item type matches expected type
                if ($item->type !== $itemData['expected_type']) {
                    $mismatchedTypes[] = [
                        'item_name' => $item->name,
                        'expected_type' => $itemData['expected_type'],
                        'actual_type' => $item->type,
                        'action' => 'using_actual_type'
                    ];
                    
                    Log::warning("Item type mismatch, using actual type from database", [
                        'item_name' => $item->name,
                        'expected_type' => $itemData['expected_type'],
                        'actual_type' => $item->type
                    ]);
                }

                $newItem = MasterAhspItem::create([
                    'master_ahsp_id' => $masterAhsp->id,
                    'category' => $item->type, // ✅ Use actual item type from database, not Excel section
                    'item_id' => $item->id,
                    'coefficient' => $itemData['coefficient'],
                    'sort_order' => $index,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
                
                $createdItems[] = [
                    'id' => $newItem->id,
                    'category' => $newItem->category,
                    'item_name' => $item->name,
                    'item_type' => $item->type,
                    'coefficient' => $newItem->coefficient,
                    'excel_code' => $itemData['excel_code']
                ];
                
                $createdCount++;
                echo "   ✅ {$item->name} ({$item->type}) - {$itemData['coefficient']}\n";
                
            } else {
                $missingItems[] = $itemData['item_name'];
                echo "   ❌ NOT FOUND: {$itemData['item_name']}\n";
            }
        }

        // Final validation - check if we have correct categories
        $finalItems = $masterAhsp->fresh()->items()->with('item')->get();
        $finalSummary = [
            'materials' => $finalItems->where('category', 'material')->count(),
            'labor' => $finalItems->where('category', 'labor')->count(),
            'equipment' => $finalItems->where('category', 'equipment')->count(),
            'total' => $finalItems->count()
        ];

        // Log comprehensive summary
        $summary = [
            'master_ahsp_id' => $masterAhsp->id,
            'master_ahsp_code' => $masterAhsp->code,
            'operation' => 'fix_excel_import_data',
            'items_deleted' => $deletedCount,
            'items_created' => $createdCount,
            'missing_items' => $missingItems,
            'type_mismatches' => $mismatchedTypes,
            'created_items' => $createdItems,
            'final_summary' => $finalSummary,
            'started_at' => $startTime,
            'completed_at' => Carbon::now()->toISOString(),
            'duration_seconds' => Carbon::parse($startTime)->diffInSeconds(Carbon::now()),
            'fixed_by' => 'kelasdrafter',
            'migration_file' => '2025_11_05_043506_fix_master_ahsp_excel_import_data'
        ];

        Log::info('Master AHSP Excel data fix completed successfully', $summary);

        // Store completion log
        try {
            DB::table('migration_logs')->insert([
                'migration' => '2025_11_05_043506_fix_master_ahsp_excel_import_data',
                'action' => 'fix_completed',
                'data' => json_encode($summary),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        } catch (\Exception $e) {
            Log::info('Migration logs table not available, skipping completion log');
        }

        // Console output summary
        echo "\n🎉 Master AHSP Excel data fix completed successfully!\n";
        echo "   📊 Summary:\n";
        echo "      - Items deleted: {$deletedCount}\n";
        echo "      - Items created: {$createdCount}\n";
        echo "      - Materials: {$finalSummary['materials']}\n";
        echo "      - Labor: {$finalSummary['labor']}\n";
        echo "      - Equipment: {$finalSummary['equipment']}\n";
        echo "      - Total items: {$finalSummary['total']}\n";
        
        if (!empty($missingItems)) {
            echo "   ⚠️  Missing items (need to be added to items table):\n";
            foreach ($missingItems as $missing) {
                echo "      - {$missing}\n";
            }
        }
        
        if (!empty($mismatchedTypes)) {
            echo "   🔀 Type corrections applied:\n";
            foreach ($mismatchedTypes as $mismatch) {
                echo "      - {$mismatch['item_name']}: {$mismatch['expected_type']} → {$mismatch['actual_type']}\n";
            }
        }
        
        echo "\n✅ Migration completed at " . Carbon::now()->toISOString() . "\n";
    }

    /**
     * Reverse the migrations.
     * Rollback the fix and restore original state
     */
    public function down(): void
    {
        $rollbackTime = Carbon::now()->toISOString();
        
        Log::info('🔄 kelasdrafter - Starting rollback of Master AHSP Excel data fix', [
            'rollback_started_at' => $rollbackTime,
            'migration_file' => '2025_11_05_043506_fix_master_ahsp_excel_import_data'
        ]);

        $masterAhsp = MasterAhsp::where('code', '2.2.1.57')->first();
        
        if (!$masterAhsp) {
            Log::warning('Master AHSP not found during rollback');
            echo "⚠️  Master AHSP with code 2.2.1.57 not found for rollback!\n";
            return;
        }

        // Get current items before rollback
        $currentItems = $masterAhsp->items()->with('item')->get();
        
        // Delete current fixed items
        $deletedCount = $masterAhsp->items()->delete();
        
        // Try to restore from backup if available
        try {
            $backupLog = DB::table('migration_logs')
                ->where('migration', '2025_11_05_043506_fix_master_ahsp_excel_import_data')
                ->where('action', 'backup_before_fix')
                ->latest()
                ->first();
                
            if ($backupLog) {
                $backupData = json_decode($backupLog->data, true);
                $originalItems = $backupData['backup_data'] ?? [];
                
                foreach ($originalItems as $itemData) {
                    MasterAhspItem::create([
                        'master_ahsp_id' => $masterAhsp->id,
                        'category' => $itemData['category'],
                        'item_id' => $itemData['item_id'],
                        'coefficient' => $itemData['coefficient'],
                        'sort_order' => $itemData['sort_order'],
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);
                }
                
                echo "✅ Restored " . count($originalItems) . " original items from backup\n";
            } else {
                echo "⚠️  No backup found, items deleted but not restored\n";
            }
        } catch (\Exception $e) {
            Log::error('Error during rollback restore', ['error' => $e->getMessage()]);
            echo "❌ Error restoring backup: " . $e->getMessage() . "\n";
        }

        Log::info('Master AHSP Excel data fix rollback completed', [
            'master_ahsp_id' => $masterAhsp->id,
            'items_deleted' => $deletedCount,
            'rollback_completed_at' => Carbon::now()->toISOString(),
            'rolled_back_by' => 'kelasdrafter'
        ]);

        echo "🔄 Rollback completed at " . Carbon::now()->toISOString() . "\n";
    }
};