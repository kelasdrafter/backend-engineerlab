<?php

namespace App\Http\Controllers\RAB;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\RAB\ProjectCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class ProjectCategoryController extends Controller
{
    public function index($projectId): JsonResponse
    {
        try {
            $data = ProjectCategory::with(['children', 'boqItems'])
                ->where('project_id', $projectId)
                ->root()
                ->ordered()
                ->get();

            return $this->responseSuccess('Get Data Successfully', $data, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    public function store(Request $request, $projectId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'parent_id' => 'nullable|exists:project_categories,id',
                'name' => 'required|string|max:255',
                'code' => 'nullable|string|max:50',
                'sort_order' => 'integer',
            ]);

            $validated['project_id'] = $projectId;

            $category = ProjectCategory::create($validated);

            return $this->responseSuccess('Create Data Successfully', $category, 201);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    public function show($projectId, $id): JsonResponse
    {
        try {
            $category = ProjectCategory::with(['children', 'boqItems', 'parent'])
                ->findOrFail($id);

            return $this->responseSuccess('Get Data Successfully', $category, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    public function update(Request $request, $projectId, $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'parent_id' => 'nullable|exists:project_categories,id',
                'name' => 'sometimes|required|string|max:255',
                'code' => 'nullable|string|max:50',
                'sort_order' => 'integer',
            ]);

            $category = ProjectCategory::findOrFail($id);
            $category->update($validated);

            return $this->responseSuccess('Update Data Successfully', $category, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

public function destroy($projectId, $id): JsonResponse
{
    try {
        $category = ProjectCategory::with(['children', 'boqItems'])->findOrFail($id);
        
        // ✅ Cascade delete: BOQ items → Children → Category
        DB::transaction(function () use ($category) {
            // 1. Delete all BOQ items in this category
            $category->boqItems()->delete();
            
            // 2. Recursively delete children categories and their BOQ items
            $this->deleteCategoryWithChildren($category);
            
            // 3. Delete the category itself
            $category->delete();
        });

        return $this->responseSuccess('Delete Data Successfully', null, 200);
    } catch (Exception $exception) {
        return $this->responseError($exception, [], 500);
    }
}

private function deleteCategoryWithChildren(ProjectCategory $category)
{
    foreach ($category->children as $child) {
        // Delete BOQ items in child category
        $child->boqItems()->delete();
        
        // Recursively delete grandchildren
        if ($child->children()->count() > 0) {
            $this->deleteCategoryWithChildren($child);
        }
        
        // Delete child category
        $child->delete();
    }
}

    public function total($projectId, $id): JsonResponse
    {
        try {
            $category = ProjectCategory::findOrFail($id);
            
            $total = $category->calculateTotal();
            $totalWithChildren = $category->calculateTotalWithChildren();

            return $this->responseSuccess('Calculate Successfully', [
                'category_total' => $total,
                'total_with_children' => $totalWithChildren,
                'formatted_total' => 'Rp ' . number_format($totalWithChildren, 0, ',', '.'),
            ], 200);
        } catch (Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }
}
