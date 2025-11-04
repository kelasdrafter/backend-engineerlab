<?php

namespace App\Http\Controllers\RAB;

use App\Http\Controllers\Controller;
use App\Services\RAB\ProjectAhspService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class ProjectAhspController extends Controller
{
    protected $projectAhspService;

    public function __construct(ProjectAhspService $projectAhspService)
    {
        $this->projectAhspService = $projectAhspService;
    }

    public function index(Request $request, $projectId): JsonResponse
    {
        try {
            $filters = $request->only(['source_type', 'search']);
            $data = $this->projectAhspService->getByProject($projectId, $filters);

            return $this->responseSuccess('Get Data Successfully', $data, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    public function show($projectId, $id): JsonResponse
    {
        try {
            $data = $this->projectAhspService->getById($id);

            return $this->responseSuccess('Get Data Successfully', $data, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    public function addFromMaster(Request $request, $projectId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'master_ahsp_id' => 'required|exists:master_ahsp,id',
            ]);

            $data = $this->projectAhspService->addFromMaster($projectId, $validated['master_ahsp_id']);

            return $this->responseSuccess('Add AHSP Successfully', $data, 201);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    public function createCustom(Request $request, $projectId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'code' => 'required|string|max:50',
                'name' => 'required|string',
                'unit' => 'required|string|max:20',
                'description' => 'nullable|string',
                'items' => 'required|array|min:1',
                'items.*.category' => 'required|in:material,labor,equipment',
                'items.*.item_id' => 'required|exists:items,id',
                'items.*.coefficient' => 'required|numeric|min:0',
            ]);

            $data = $this->projectAhspService->createCustom($projectId, $validated);

            return $this->responseSuccess('Create Custom AHSP Successfully', $data, 201);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    public function updateComposition(Request $request, $projectId, $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'items' => 'required|array|min:1',
                'items.*.category' => 'required|in:material,labor,equipment',
                'items.*.item_id' => 'required|exists:items,id',
                'items.*.coefficient' => 'required|numeric|min:0',
            ]);

            $data = $this->projectAhspService->updateComposition($id, $validated['items']);

            return $this->responseSuccess('Update Composition Successfully', $data, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    public function destroy($projectId, $id): JsonResponse
    {
        try {
            $this->projectAhspService->delete($id);

            return $this->responseSuccess('Delete Data Successfully', null, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    public function calculatePrice($projectId, $id): JsonResponse
    {
        try {
            $unitPrice = $this->projectAhspService->calculateUnitPrice($id);

            return $this->responseSuccess('Calculate Successfully', [
                'unit_price' => $unitPrice,
                'formatted' => 'Rp ' . number_format($unitPrice, 0, ',', '.'),
            ], 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    public function breakdown($projectId, $id): JsonResponse
    {
        try {
            $breakdown = $this->projectAhspService->getCompositionBreakdown($id);

            return $this->responseSuccess('Get Breakdown Successfully', $breakdown, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    public function syncFromMaster($projectId, $id): JsonResponse
    {
        try {
            $data = $this->projectAhspService->syncFromMaster($id);

            return $this->responseSuccess('Sync Successfully', $data, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }
}
