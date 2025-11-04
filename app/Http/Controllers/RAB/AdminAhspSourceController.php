<?php

namespace App\Http\Controllers\RAB;

use App\Http\Controllers\Controller;
use App\Services\RAB\AhspSourceService;
use App\Http\Resources\RAB\AhspSourceResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class AdminAhspSourceController extends Controller
{
    protected $ahspSourceService;

    public function __construct(AhspSourceService $ahspSourceService)
    {
        $this->ahspSourceService = $ahspSourceService;
    }

    /**
     * Display a listing of AHSP sources
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);

            $data = QueryBuilder::for(\App\Models\RAB\AhspSource::class)
                ->allowedFilters([
                    'code',
                    'name',
                    AllowedFilter::exact('is_active'),
                    AllowedFilter::callback('search', function ($query, $value) {
                        $query->where(function ($q) use ($value) {
                            $q->where('name', 'like', "%{$value}%")
                                ->orWhere('code', 'like', "%{$value}%");
                        });
                    }),
                ])
                ->allowedSorts(['code', 'name', 'sort_order', 'created_at'])
                ->defaultSort('sort_order')
                ->paginate($perPage);

            return $this->responseSuccess('Get Data Successfully', $data, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    /**
     * Store a newly created AHSP source
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'code' => 'required|string|max:20|unique:ahsp_sources,code',
                'name' => 'required|string|max:100',
                'description' => 'nullable|string',
                'icon' => 'nullable|string|max:50',
                'color' => 'nullable|string|max:20',
                'is_active' => 'boolean',
                'sort_order' => 'integer',
            ]);

            $ahspSource = $this->ahspSourceService->create($validated);

            return $this->responseSuccess('Create Data Successfully', new AhspSourceResource($ahspSource), 201);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    /**
     * Display the specified AHSP source
     */
    public function show($id): JsonResponse
    {
        try {
            $ahspSource = $this->ahspSourceService->getById($id);

            return $this->responseSuccess('Get Data Successfully', new AhspSourceResource($ahspSource), 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    /**
     * Update the specified AHSP source
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'code' => 'sometimes|required|string|max:20|unique:ahsp_sources,code,' . $id,
                'name' => 'sometimes|required|string|max:100',
                'description' => 'nullable|string',
                'icon' => 'nullable|string|max:50',
                'color' => 'nullable|string|max:20',
                'is_active' => 'boolean',
                'sort_order' => 'integer',
            ]);

            $ahspSource = $this->ahspSourceService->update($id, $validated);

            return $this->responseSuccess('Update Data Successfully', new AhspSourceResource($ahspSource), 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    /**
     * Remove the specified AHSP source
     */
    public function destroy($id): JsonResponse
    {
        try {
            $this->ahspSourceService->delete($id);

            return $this->responseSuccess('Delete Data Successfully', null, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    /**
     * Toggle active status
     */
    public function toggleActive($id): JsonResponse
    {
        try {
            $ahspSource = $this->ahspSourceService->toggleActive($id);

            return $this->responseSuccess('Status Updated Successfully', new AhspSourceResource($ahspSource), 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    /**
     * Get usage statistics
     */
    public function stats($id): JsonResponse
    {
        try {
            $stats = $this->ahspSourceService->getUsageStats($id);

            return $this->responseSuccess('Get Stats Successfully', $stats, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }
}
