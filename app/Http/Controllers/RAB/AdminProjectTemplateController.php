<?php

namespace App\Http\Controllers\RAB;

use App\Http\Controllers\Controller;
use App\Models\RAB\ProjectTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class AdminProjectTemplateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);

            $data = QueryBuilder::for(ProjectTemplate::class)
                ->with(['region', 'ahspSource', 'creator'])
                ->allowedFilters([
                    'name',
                    AllowedFilter::exact('is_global'),
                    AllowedFilter::exact('is_active'),
                    AllowedFilter::exact('ahsp_source_id'),
                ])
                ->allowedSorts(['name', 'created_at'])
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
                'ahsp_source_id' => 'required|exists:ahsp_sources,id',
                'is_global' => 'boolean',
                'is_active' => 'boolean',
            ]);

            $validated['created_by'] = auth()->id();

            $template = ProjectTemplate::create($validated);

            return $this->responseSuccess('Create Data Successfully', $template, 201);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $template = ProjectTemplate::with([
                'region',
                'ahspSource',
                'rootCategories.children.items'
            ])->findOrFail($id);

            return $this->responseSuccess('Get Data Successfully', $template, 200);
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
                'is_global' => 'boolean',
                'is_active' => 'boolean',
            ]);

            $template = ProjectTemplate::findOrFail($id);
            $template->update($validated);

            return $this->responseSuccess('Update Data Successfully', $template, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $template = ProjectTemplate::findOrFail($id);
            $template->delete();

            return $this->responseSuccess('Delete Data Successfully', null, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }
}
