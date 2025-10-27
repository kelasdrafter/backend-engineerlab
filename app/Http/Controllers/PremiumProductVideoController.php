<?php

namespace App\Http\Controllers;

use App\Models\PremiumProductVideo;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PremiumProductVideoController extends Controller
{
    /**
     * Display a listing of videos for a product (Public)
     */
    public function index($productId): JsonResponse
    {
        try {
            $videos = PremiumProductVideo::where('premium_product_id', $productId)
                ->orderBy('sort_order', 'asc')
                ->get();

            return $this->responseSuccess('Get Data Successfully', $videos, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception->getMessage(), [], 500);
        }
    }

    /**
     * Store a newly created video (Admin Only)
     */
    public function store(Request $request, $productId): JsonResponse
    {
        try {
            // Check max 9 videos
            $count = PremiumProductVideo::where('premium_product_id', $productId)->count();
            if ($count >= 9) {
                return $this->responseError('Maximum 9 videos allowed', [], 400);
            }

            $validated = $request->validate([
                'video_url' => 'required|string',
                'sort_order' => 'nullable|integer',
            ]);

            $validated['premium_product_id'] = $productId;
            $data = PremiumProductVideo::create($validated);

            return $this->responseSuccess('Create Data Successfully', $data, 201);
        } catch (Exception $exception) {
            return $this->responseError($exception->getMessage(), [], 500);
        }
    }

    /**
     * Update the specified video (Admin Only)
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $video = PremiumProductVideo::findOrFail($id);

            $validated = $request->validate([
                'video_url' => 'sometimes|string',
                'sort_order' => 'nullable|integer',
            ]);

            $video->update($validated);

            return $this->responseSuccess('Update Data Successfully', $video, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception->getMessage(), [], 500);
        }
    }

    /**
     * Remove the specified video (Admin Only)
     */
    public function destroy($id): JsonResponse
    {
        try {
            $video = PremiumProductVideo::findOrFail($id);
            $video->delete();

            return $this->responseSuccess('Delete Data Successfully', $video, 200);
        } catch (Exception $exception) {
            return $this->responseError($exception->getMessage(), [], 500);
        }
    }
}