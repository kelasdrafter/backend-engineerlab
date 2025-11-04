<?php

namespace App\Services\RAB;

use App\Models\RAB\Project;
use App\Models\RAB\ProjectBoqItem;
use App\Models\RAB\ProjectAhsp;
use Illuminate\Support\Facades\DB;
use Exception;

class CalculationService
{
    /**
     * Calculate project totals
     */
    public function calculateProjectTotals($projectId)
    {
        $project = Project::with(['categories.boqItems'])->findOrFail($projectId);

        return [
            'boq_total' => $project->calculateTotalBoq(),
            'overhead' => $project->calculateOverhead(),
            'profit' => $project->calculateProfit(),
            'subtotal' => $project->calculateSubtotal(),
            'ppn' => $project->calculatePpn(),
            'grand_total' => $project->calculateGrandTotal(),
        ];
    }

    /**
     * Calculate BOQ item total
     */
    public function calculateBoqItemTotal($boqItemId)
    {
        $boqItem = ProjectBoqItem::findOrFail($boqItemId);

        if ($boqItem->isAhsp() && $boqItem->projectAhsp) {
            $boqItem->unit_price = $this->calculateAhspUnitPrice($boqItem->project_ahsp_id);
        }

        $boqItem->total_price = $boqItem->volume * $boqItem->unit_price;
        $boqItem->save();

        return [
            'volume' => $boqItem->volume,
            'unit_price' => $boqItem->unit_price,
            'total_price' => $boqItem->total_price,
        ];
    }

    /**
     * Calculate AHSP unit price
     */
    public function calculateAhspUnitPrice($projectAhspId)
    {
        $projectAhsp = ProjectAhsp::with([
            'project',
            'items.item'
        ])->findOrFail($projectAhspId);

        $totalPrice = 0;

        foreach ($projectAhsp->items as $ahspItem) {
            $itemPrice = \App\Models\RAB\ProjectItemPrice::where('project_id', $projectAhsp->project_id)
                ->where('item_id', $ahspItem->item_id)
                ->first();

            if ($itemPrice) {
                $totalPrice += $itemPrice->price * $ahspItem->coefficient;
            }
        }

        return $totalPrice;
    }

    /**
     * Get AHSP composition breakdown with prices
     */
    public function getAhspBreakdown($projectAhspId)
    {
        $projectAhsp = ProjectAhsp::with([
            'project',
            'items.item'
        ])->findOrFail($projectAhspId);

        $breakdown = [
            'ahsp' => [
                'code' => $projectAhsp->code,
                'name' => $projectAhsp->name,
                'unit' => $projectAhsp->unit,
            ],
            'materials' => [],
            'labor' => [],
            'equipment' => [],
            'totals' => [
                'material_total' => 0,
                'labor_total' => 0,
                'equipment_total' => 0,
                'unit_price' => 0,
            ],
        ];

        foreach ($projectAhsp->items as $ahspItem) {
            $itemPrice = \App\Models\RAB\ProjectItemPrice::where('project_id', $projectAhsp->project_id)
                ->where('item_id', $ahspItem->item_id)
                ->first();

            $price = $itemPrice ? $itemPrice->price : 0;
            $total = $price * $ahspItem->coefficient;

            $itemData = [
                'code' => $ahspItem->item->code,
                'name' => $ahspItem->item->name,
                'unit' => $ahspItem->item->unit,
                'coefficient' => (float) $ahspItem->coefficient,
                'unit_price' => (float) $price,
                'total_price' => (float) $total,
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

        $breakdown['totals']['unit_price'] = 
            $breakdown['totals']['material_total'] + 
            $breakdown['totals']['labor_total'] + 
            $breakdown['totals']['equipment_total'];

        return $breakdown;
    }

    /**
     * Recalculate all BOQ prices in project
     */
    public function recalculateProject($projectId)
    {
        DB::beginTransaction();

        try {
            $project = Project::with(['categories.boqItems'])->findOrFail($projectId);

            $recalculatedCount = 0;

            foreach ($project->categories as $category) {
                foreach ($category->boqItems as $boqItem) {
                    if ($boqItem->isAhsp() && $boqItem->projectAhsp) {
                        // Update unit price from AHSP calculation
                        $boqItem->unit_price = $this->calculateAhspUnitPrice($boqItem->project_ahsp_id);
                        $boqItem->total_price = $boqItem->volume * $boqItem->unit_price;
                        $boqItem->save();

                        $recalculatedCount++;
                    }
                }
            }

            DB::commit();

            return [
                'success' => true,
                'recalculated_items' => $recalculatedCount,
                'totals' => $this->calculateProjectTotals($projectId),
            ];
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get category total (including children)
     */
    public function getCategoryTotal($categoryId)
    {
        $category = \App\Models\RAB\ProjectCategory::with([
            'boqItems',
            'children'
        ])->findOrFail($categoryId);

        return $category->calculateTotalWithChildren();
    }

    /**
     * Get project summary with detailed breakdown
     */
    public function getProjectSummary($projectId)
    {
        $project = Project::with([
            'region',
            'ahspSource',
            'rootCategories.children.boqItems'
        ])->findOrFail($projectId);

        $boqTotal = $project->calculateTotalBoq();
        $overhead = $project->calculateOverhead();
        $profit = $project->calculateProfit();
        $subtotal = $project->calculateSubtotal();
        $ppn = $project->calculatePpn();
        $grandTotal = $project->calculateGrandTotal();

        return [
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'region' => $project->region->name,
                'ahsp_source' => $project->ahspSource->name,
                'status' => $project->status,
            ],
            'breakdown' => [
                'boq_total' => [
                    'amount' => $boqTotal,
                    'formatted' => 'Rp ' . number_format($boqTotal, 0, ',', '.'),
                ],
                'overhead' => [
                    'percentage' => $project->overhead_percentage,
                    'amount' => $overhead,
                    'formatted' => 'Rp ' . number_format($overhead, 0, ',', '.'),
                ],
                'profit' => [
                    'percentage' => $project->profit_percentage,
                    'amount' => $profit,
                    'formatted' => 'Rp ' . number_format($profit, 0, ',', '.'),
                ],
                'subtotal' => [
                    'amount' => $subtotal,
                    'formatted' => 'Rp ' . number_format($subtotal, 0, ',', '.'),
                ],
                'ppn' => [
                    'percentage' => $project->ppn_percentage,
                    'amount' => $ppn,
                    'formatted' => 'Rp ' . number_format($ppn, 0, ',', '.'),
                ],
                'grand_total' => [
                    'amount' => $grandTotal,
                    'formatted' => 'Rp ' . number_format($grandTotal, 0, ',', '.'),
                ],
            ],
            'statistics' => [
                'total_categories' => $project->categories()->count(),
                'total_boq_items' => ProjectBoqItem::whereHas('projectCategory', function ($q) use ($projectId) {
                    $q->where('project_id', $projectId);
                })->count(),
                'total_ahsp_used' => $project->projectAhsp()->count(),
            ],
        ];
    }

    /**
     * Compare two projects
     */
    public function compareProjects($projectId1, $projectId2)
    {
        $project1 = $this->getProjectSummary($projectId1);
        $project2 = $this->getProjectSummary($projectId2);

        $difference = $project1['breakdown']['grand_total']['amount'] - $project2['breakdown']['grand_total']['amount'];
        $percentageDiff = $project2['breakdown']['grand_total']['amount'] > 0 
            ? ($difference / $project2['breakdown']['grand_total']['amount']) * 100 
            : 0;

        return [
            'project1' => $project1,
            'project2' => $project2,
            'comparison' => [
                'difference' => $difference,
                'percentage_difference' => $percentageDiff,
                'cheaper_project' => $difference < 0 ? $projectId1 : $projectId2,
            ],
        ];
    }

    /**
     * Format currency
     */
    public function formatCurrency($amount)
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}
