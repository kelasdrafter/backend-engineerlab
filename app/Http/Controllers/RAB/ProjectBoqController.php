<?php

namespace App\Http\Controllers\RAB;

use App\Http\Controllers\Controller;
use App\Models\RAB\ProjectBoqItem;
use App\Models\RAB\ProjectCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;

class ProjectBoqController extends Controller
{
    public function index(Request $request, $projectId): JsonResponse
    {
        try {
            $categoryId = $request->get('category_id');
            
            $query = ProjectBoqItem::with(['projectCategory', 'projectAhsp']);
            
            if ($categoryId) {
                $query->where('project_category_id', $categoryId);
            } else {
                $query->whereHas('projectCategory', function($q) use ($projectId) {
                    $q->where('project_id', $projectId);
                });
            }
            
            $data = $query->ordered()->get();

            return $this->responseSuccess('Get Data Successfully', $data, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    public function store(Request $request, $projectId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'project_category_id' => 'required|exists:project_categories,id',
                'item_type' => 'required|in:ahsp,custom',
                'project_ahsp_id' => 'required_if:item_type,ahsp|exists:project_ahsp,id',
                'code' => 'required_if:item_type,custom|string|max:50',
                'name' => 'required_if:item_type,custom|string',
                'unit' => 'required_if:item_type,custom|string|max:20',
                'volume' => 'required|numeric|min:0',
                'unit_price' => 'required|numeric|min:0',
                'notes' => 'nullable|string',
            ]);

            DB::beginTransaction();

            // If AHSP, get details from project_ahsp
            if ($validated['item_type'] === 'ahsp') {
                $projectAhsp = \App\Models\RAB\ProjectAhsp::findOrFail($validated['project_ahsp_id']);
                $validated['code'] = $projectAhsp->code;
                $validated['name'] = $projectAhsp->name;
                $validated['unit'] = $projectAhsp->unit;
            }

            $boqItem = ProjectBoqItem::create($validated);

            DB::commit();

            return $this->responseSuccess('Create Data Successfully', $boqItem->load('projectAhsp'), 201);
        } catch (Exception $exception) {
            DB::rollBack();
            return $this->responseError($exception, [], 500);
        }
    }

    public function update(Request $request, $projectId, $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'volume' => 'sometimes|required|numeric|min:0',
                'unit_price' => 'sometimes|required|numeric|min:0',
                'notes' => 'nullable|string',
            ]);

            $boqItem = ProjectBoqItem::findOrFail($id);
            $boqItem->update($validated);

            return $this->responseSuccess('Update Data Successfully', $boqItem, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    public function destroy($projectId, $id): JsonResponse
    {
        try {
            $boqItem = ProjectBoqItem::findOrFail($id);
            $boqItem->delete();

            return $this->responseSuccess('Delete Data Successfully', null, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    public function recalculate($projectId, $id): JsonResponse
    {
        try {
            $boqItem = ProjectBoqItem::findOrFail($id);
            
            if ($boqItem->isAhsp()) {
                $boqItem->updateUnitPriceFromAhsp();
            }
            
            $boqItem->recalculateTotal();

            return $this->responseSuccess('Recalculate Successfully', [
                'volume' => $boqItem->volume,
                'unit_price' => $boqItem->unit_price,
                'total_price' => $boqItem->total_price,
                'formatted_total' => $boqItem->formatted_total_price,
            ], 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }
}
