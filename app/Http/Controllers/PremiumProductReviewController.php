<?php

namespace App\Http\Controllers;

use App\Models\PremiumProductReview;
use App\Models\File;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PremiumProductReviewController extends Controller
{
    /**
     * Display a listing of reviews for a product (Public - only published)
     */
    public function index($productId): JsonResponse
    {
        try {
            $reviews = PremiumProductReview::where('premium_product_id', $productId)
                ->where('is_published', true)
                ->orderBy('created_at', 'desc')
                ->get();

            return $this->responseSuccess('Get Data Successfully', $reviews, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception->getMessage(), [], 500);
        }
    }

    /**
     * Store a newly created review (Admin Only)
     */
    public function store(Request $request, $productId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'reviewer_name' => 'required|string|max:255',
                'reviewer_photo' => 'nullable|string',
                'review_text' => 'required|string',
                'is_published' => 'boolean',
            ]);

            $validated['premium_product_id'] = $productId;
            $data = PremiumProductReview::create($validated);

            return $this->responseSuccess('Create Data Successfully', $data, 201);
        } catch (Exception $exception) {
            return $this->responseError($exception->getMessage(), [], 500);
        }
    }

    /**
     * Update the specified review (Admin Only)
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $review = PremiumProductReview::findOrFail($id);

            $validated = $request->validate([
                'reviewer_name' => 'sometimes|string|max:255',
                'reviewer_photo' => 'nullable|string',
                'review_text' => 'sometimes|string',
                'is_published' => 'boolean',
            ]);

            // Delete old photo if updated
            if ($request->has('reviewer_photo') && $request->reviewer_photo != $review->reviewer_photo && $review->reviewer_photo) {
                try {
                    $baseUrl = rtrim(env('AWS_URL'), '/');
                    $key = ltrim(str_replace($baseUrl, '', $review->reviewer_photo), '/');
                    File::deleteByPath($key);
                } catch (Exception $e) {}
            }

            $review->update($validated);

            return $this->responseSuccess('Update Data Successfully', $review, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception->getMessage(), [], 500);
        }
    }

    /**
     * Remove the specified review (Admin Only)
     */
    public function destroy($id): JsonResponse
    {
        try {
            $review = PremiumProductReview::findOrFail($id);

            // Delete photo from S3 if exists
            if ($review->reviewer_photo) {
                try {
                    $baseUrl = rtrim(env('AWS_URL'), '/');
                    $key = ltrim(str_replace($baseUrl, '', $review->reviewer_photo), '/');
                    File::deleteByPath($key);
                } catch (Exception $e) {}
            }

            $review->delete();

            return $this->responseSuccess('Delete Data Successfully', $review, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception->getMessage(), [], 500);
        }
    }
}