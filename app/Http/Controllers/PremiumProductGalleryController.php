<?php

namespace App\Http\Controllers;

use App\Models\PremiumProductGallery;
use App\Models\PremiumProduct;
use App\Models\File;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PremiumProductGalleryController extends Controller
{
    /**
     * Display a listing of galleries for a product (Public)
     */
    public function index($productId): JsonResponse
    {
        try {
            $galleries = PremiumProductGallery::where('premium_product_id', $productId)
                ->orderBy('sort_order', 'asc')
                ->get();

            return $this->responseSuccess('Get Data Successfully', $galleries, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception->getMessage(), [], 500);
        }
    }

    /**
     * Store a newly created gallery (Admin Only)
     */
    public function store(Request $request, $productId): JsonResponse
    {
        try {
            // Check max 6 galleries
            $count = PremiumProductGallery::where('premium_product_id', $productId)->count();
            if ($count >= 6) {
                return $this->responseError('Maximum 6 galleries allowed', [], 400);
            }

            $validated = $request->validate([
                'image_url' => 'required|string',
                'sort_order' => 'nullable|integer',
            ]);

            $validated['premium_product_id'] = $productId;
            $data = PremiumProductGallery::create($validated);

            return $this->responseSuccess('Create Data Successfully', $data, 201);
        } catch (Exception $exception) {
            return $this->responseError($exception->getMessage(), [], 500);
        }
    }

    /**
     * Update the specified gallery (Admin Only)
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $gallery = PremiumProductGallery::findOrFail($id);

            $validated = $request->validate([
                'image_url' => 'sometimes|string',
                'sort_order' => 'nullable|integer',
            ]);

            // Delete old image if updated
            if ($request->has('image_url') && $request->image_url != $gallery->image_url) {
                try {
                    $baseUrl = rtrim(env('AWS_URL'), '/');
                    $key = ltrim(str_replace($baseUrl, '', $gallery->image_url), '/');
                    File::deleteByPath($key);
                } catch (Exception $e) {}
            }

            $gallery->update($validated);

            return $this->responseSuccess('Update Data Successfully', $gallery, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception->getMessage(), [], 500);
        }
    }

    /**
     * Remove the specified gallery (Admin Only)
     */
    public function destroy($id): JsonResponse
    {
        try {
            $gallery = PremiumProductGallery::findOrFail($id);

            // Delete image from S3
            try {
                $baseUrl = rtrim(env('AWS_URL'), '/');
                $key = ltrim(str_replace($baseUrl, '', $gallery->image_url), '/');
                File::deleteByPath($key);
            } catch (Exception $e) {}

            $gallery->delete();

            return $this->responseSuccess('Delete Data Successfully', $gallery, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception->getMessage(), [], 500);
        }
    }
}