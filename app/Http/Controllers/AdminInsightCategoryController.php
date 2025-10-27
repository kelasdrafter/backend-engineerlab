<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\InsightCategoryResource;
use App\Models\InsightCategory;
use Illuminate\Http\JsonResponse;

class AdminInsightCategoryController extends Controller
{
    /**
     * Store a newly created category (Admin only)
     * 
     * @param StoreCategoryRequest $request
     * @return JsonResponse
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $this->authorize('create', InsightCategory::class);

        $category = InsightCategory::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully',
            'data' => new InsightCategoryResource($category),
        ], 201);
    }

    /**
     * Update the specified category (Admin only)
     * 
     * @param UpdateCategoryRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateCategoryRequest $request, int $id): JsonResponse
    {
        $category = InsightCategory::findOrFail($id);
        
        $this->authorize('update', $category);

        $category->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully',
            'data' => new InsightCategoryResource($category->fresh()),
        ]);
    }

    /**
     * Remove the specified category (Admin only)
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $category = InsightCategory::findOrFail($id);
        
        $this->authorize('delete', $category);

        // Check if category has insights
        if ($category->insights()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with existing insights',
            ], 422);
        }

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category deleted successfully',
        ]);
    }
}