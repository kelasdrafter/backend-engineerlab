<?php

namespace App\Http\Controllers;

use App\Models\PremiumProductCompatibility;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PremiumProductCompatibilityController extends Controller
{
    /**
     * Display a listing of compatibilities for a product (Public)
     */
    public function index($productId): JsonResponse
    {
        try {
            $compatibilities = PremiumProductCompatibility::where('premium_product_id', $productId)
                ->orderBy('sort_order', 'asc')
                ->get();

            return $this->responseSuccess('Get Data Successfully', $compatibilities, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception->getMessage(), [], 500);
        }
    }

    /**
     * Store a newly created compatibility (Admin Only)
     */
    public function store(Request $request, $productId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'compatibility_text' => 'required|string|max:255',
                'sort_order' => 'nullable|integer',
            ]);

            $validated['premium_product_id'] = $productId;
            $data = PremiumProductCompatibility::create($validated);

            return $this->responseSuccess('Create Data Successfully', $data, 201);
        } catch (Exception $exception) {
            return $this->responseError($exception->getMessage(), [], 500);
        }
    }

    /**
     * Update the specified compatibility (Admin Only)
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $compatibility = PremiumProductCompatibility::findOrFail($id);

            $validated = $request->validate([
                'compatibility_text' => 'sometimes|string|max:255',
                'sort_order' => 'nullable|integer',
            ]);

            $compatibility->update($validated);

            return $this->responseSuccess('Update Data Successfully', $compatibility, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception->getMessage(), [], 500);
        }
    }

    /**
     * Remove the specified compatibility (Admin Only)
     */
    public function destroy($id): JsonResponse
    {
        try {
            $compatibility = PremiumProductCompatibility::findOrFail($id);
            $compatibility->delete();

            return $this->responseSuccess('Delete Data Successfully', $compatibility, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception->getMessage(), [], 500);
        }
    }
}