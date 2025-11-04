<?php

namespace App\Http\Controllers\RAB;

use App\Http\Controllers\Controller;
use App\Models\RAB\Region;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class AdminRegionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);

            $data = QueryBuilder::for(Region::class)
                ->allowedFilters([
                    'province',
                    'city',
                    AllowedFilter::exact('type'),
                    AllowedFilter::exact('is_active'),
                ])
                ->allowedSorts(['province', 'city', 'created_at'])
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
                'code' => 'required|string|max:20|unique:regions,code',
                'name' => 'required|string|max:100',
                'province' => 'required|string|max:100',
                'city' => 'required|string|max:100',
                'type' => 'required|in:city,regency',
                'is_active' => 'boolean',
            ]);

            $validated['created_by'] = auth()->id();

            $region = Region::create($validated);

            return $this->responseSuccess('Create Data Successfully', $region, 201);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $region = Region::findOrFail($id);

            return $this->responseSuccess('Get Data Successfully', $region, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'code' => 'sometimes|required|string|max:20|unique:regions,code,' . $id,
                'name' => 'sometimes|required|string|max:100',
                'province' => 'sometimes|required|string|max:100',
                'city' => 'sometimes|required|string|max:100',
                'type' => 'sometimes|required|in:city,regency',
                'is_active' => 'boolean',
            ]);

            $region = Region::findOrFail($id);
            $region->update($validated);

            return $this->responseSuccess('Update Data Successfully', $region, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $region = Region::findOrFail($id);
            $region->delete();

            return $this->responseSuccess('Delete Data Successfully', null, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }
}
