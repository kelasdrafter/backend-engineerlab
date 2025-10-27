<?php

namespace App\Http\Controllers;

use App\Http\Resources\InsightCategoryResource;
use App\Models\InsightCategory;
use Illuminate\Http\JsonResponse;

class InsightCategoryController extends Controller
{
    /**
     * Display active categories (Public)
     * 
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $categories = InsightCategory::active()
            ->ordered()
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Categories retrieved successfully',
            'data' => InsightCategoryResource::collection($categories),
        ]);
    }
}