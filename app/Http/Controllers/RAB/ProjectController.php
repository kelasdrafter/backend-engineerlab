<?php

namespace App\Http\Controllers\RAB;

use App\Http\Controllers\Controller;
use App\Services\RAB\ProjectService;
use App\Http\Resources\RAB\ProjectResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class ProjectController extends Controller
{
    protected $projectService;

    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);

            $data = QueryBuilder::for(\App\Models\RAB\Project::class)
                ->with(['region', 'ahspSource', 'owner'])
                ->where('created_by', auth()->id()) // Multi-tenant
                ->allowedFilters([
                    'name',
                    AllowedFilter::exact('status'),
                    AllowedFilter::exact('ahsp_source_id'),
                    AllowedFilter::exact('region_id'),
                    AllowedFilter::exact('is_active'),
                ])
                ->allowedSorts(['name', 'status', 'created_at', 'updated_at'])
                ->defaultSort('-created_at')
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
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'region_id' => 'required|exists:regions,id',
                'template_id' => 'nullable|exists:project_templates,id',
                'ahsp_source_id' => 'required_without:template_id|exists:ahsp_sources,id',
                'overhead_percentage' => 'numeric|min:0|max:100',
                'profit_percentage' => 'numeric|min:0|max:100',
                'ppn_percentage' => 'numeric|min:0|max:100',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
            ]);

            $validated['created_by'] = auth()->id();

            $project = $this->projectService->create($validated);

            return $this->responseSuccess('Create Data Successfully', new ProjectResource($project), 201);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $project = $this->projectService->getById($id);

            // Check ownership
            if ($project->created_by != auth()->id()) {
                return $this->responseError(new Exception('Unauthorized'), [], 403);
            }

            return $this->responseSuccess('Get Data Successfully', new ProjectResource($project), 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'overhead_percentage' => 'numeric|min:0|max:100',
                'profit_percentage' => 'numeric|min:0|max:100',
                'ppn_percentage' => 'numeric|min:0|max:100',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'status' => 'in:draft,active,completed,cancelled',
            ]);

            $project = $this->projectService->update($id, $validated);

            return $this->responseSuccess('Update Data Successfully', new ProjectResource($project), 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $this->projectService->delete($id);

            return $this->responseSuccess('Delete Data Successfully', null, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    public function summary($id): JsonResponse
    {
        try {
            $summary = $this->projectService->getSummary($id);

            return $this->responseSuccess('Get Summary Successfully', $summary, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    public function recalculate($id): JsonResponse
    {
        try {
            $result = $this->projectService->recalculateAllPrices($id);

            return $this->responseSuccess('Recalculate Successfully', $result, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }
}
