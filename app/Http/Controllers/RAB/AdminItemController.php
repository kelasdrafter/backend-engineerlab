<?php

namespace App\Http\Controllers\RAB;

use App\Http\Controllers\Controller;
use App\Models\RAB\Item;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class AdminItemController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 15);

            $data = QueryBuilder::for(Item::class)
                ->with('creator')
                ->allowedFilters([
                    'code',
                    'name',
                    AllowedFilter::exact('type'),
                    AllowedFilter::exact('is_active'),
                ])
                ->allowedSorts(['code', 'name', 'type', 'created_at'])
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
                'code' => 'required|string|max:50|unique:items,code',
                'name' => 'required|string',
                'type' => 'required|in:material,labor,equipment',
                'unit' => 'required|string|max:20',
                'description' => 'nullable|string',
                'is_active' => 'boolean',
            ]);

            $validated['created_by'] = auth()->id();

            $item = Item::create($validated);

            return $this->responseSuccess('Create Data Successfully', $item, 201);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $item = Item::with(['prices.region', 'creator'])->findOrFail($id);

            return $this->responseSuccess('Get Data Successfully', $item, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'code' => 'sometimes|required|string|max:50|unique:items,code,' . $id,
                'name' => 'sometimes|required|string',
                'type' => 'sometimes|required|in:material,labor,equipment',
                'unit' => 'sometimes|required|string|max:20',
                'description' => 'nullable|string',
                'is_active' => 'boolean',
            ]);

            $item = Item::findOrFail($id);
            $item->update($validated);

            return $this->responseSuccess('Update Data Successfully', $item, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $item = Item::findOrFail($id);
            $item->delete();

            return $this->responseSuccess('Delete Data Successfully', null, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }
}
