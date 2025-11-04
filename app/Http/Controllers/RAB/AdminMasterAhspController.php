<?php

namespace App\Http\Controllers\RAB;

use App\Http\Controllers\Controller;
use App\Services\RAB\MasterAhspService;
use App\Http\Resources\RAB\MasterAhspResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class AdminMasterAhspController extends Controller
{
    protected $masterAhspService;

    public function __construct(MasterAhspService $masterAhspService)
    {
        $this->masterAhspService = $masterAhspService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);

            $data = QueryBuilder::for(\App\Models\RAB\MasterAhsp::class)
                ->with(['ahspSource', 'creator'])
                ->allowedFilters([
                    'code',
                    'name',
                    AllowedFilter::exact('ahsp_source_id'),
                    AllowedFilter::exact('is_active'),
                    AllowedFilter::callback('search', function ($query, $value) {
                        $query->where(function ($q) use ($value) {
                            $q->where('code', 'like', "%{$value}%")
                                ->orWhere('name', 'like', "%{$value}%");
                        });
                    }),
                ])
                ->allowedSorts(['code', 'name', 'created_at'])
                ->paginate($perPage);

            return $this->responseSuccess('Get Data Successfully', $data, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'ahsp_source_id' => 'required|exists:ahsp_sources,id',
                'code' => 'required|string|max:50',
                'name' => 'required|string',
                'unit' => 'required|string|max:20',
                'description' => 'nullable|string',
                'is_active' => 'boolean',
                'items' => 'required|array|min:1',
                'items.*.category' => 'required|in:material,labor,equipment',
                'items.*.item_id' => 'required|exists:items,id',
                'items.*.coefficient' => 'required|numeric|min:0',
            ]);

            $ahsp = $this->masterAhspService->create($validated);

            return $this->responseSuccess('Create Data Successfully', new MasterAhspResource($ahsp), 201);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $ahsp = $this->masterAhspService->getById($id);

            return $this->responseSuccess('Get Data Successfully', new MasterAhspResource($ahsp), 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'ahsp_source_id' => 'sometimes|required|exists:ahsp_sources,id',
                'code' => 'sometimes|required|string|max:50',
                'name' => 'sometimes|required|string',
                'unit' => 'sometimes|required|string|max:20',
                'description' => 'nullable|string',
                'is_active' => 'boolean',
                'items' => 'sometimes|array|min:1',
                'items.*.category' => 'required_with:items|in:material,labor,equipment',
                'items.*.item_id' => 'required_with:items|exists:items,id',
                'items.*.coefficient' => 'required_with:items|numeric|min:0',
            ]);

            $ahsp = $this->masterAhspService->update($id, $validated);

            return $this->responseSuccess('Update Data Successfully', new MasterAhspResource($ahsp), 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $this->masterAhspService->delete($id);

            return $this->responseSuccess('Delete Data Successfully', null, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    public function calculatePrice(Request $request, $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'region_id' => 'required|exists:regions,id',
            ]);

            $unitPrice = $this->masterAhspService->calculateUnitPrice($id, $validated['region_id']);

            return $this->responseSuccess('Calculate Successfully', [
                'unit_price' => $unitPrice,
                'formatted' => 'Rp ' . number_format($unitPrice, 0, ',', '.'),
            ], 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    public function breakdown(Request $request, $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'region_id' => 'required|exists:regions,id',
            ]);

            $breakdown = $this->masterAhspService->getCompositionBreakdown($id, $validated['region_id']);

            return $this->responseSuccess('Get Breakdown Successfully', $breakdown, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    public function duplicate(Request $request, $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'code' => 'required|string|max:50',
                'name' => 'required|string',
                'ahsp_source_id' => 'sometimes|exists:ahsp_sources,id',
            ]);

            $ahsp = $this->masterAhspService->duplicate($id, $validated);

            return $this->responseSuccess('Duplicate Successfully', new MasterAhspResource($ahsp), 201);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }
}
