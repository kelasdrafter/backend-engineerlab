<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInsightRequest;
use App\Http\Requests\UpdateInsightRequest;
use App\Http\Resources\InsightResource;
use App\Http\Resources\InsightDetailResource;
use App\Models\Insight;
use App\Services\InsightService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class InsightController extends Controller
{
    protected InsightService $insightService;

    public function __construct(InsightService $insightService)
    {
        $this->insightService = $insightService;
    }

    /**
     * Display a listing of insights (Public)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'category_id',
            'category_slug',
            'user_id',
            'search',
            'sort_by',
            'sort_order'
        ]);

        $perPage = $request->input('per_page', 15);
        
        $insights = $this->insightService->getAllInsights($filters, $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Insights retrieved successfully',
            'data' => InsightResource::collection($insights),
            'meta' => [
                'current_page' => $insights->currentPage(),
                'from' => $insights->firstItem(),
                'last_page' => $insights->lastPage(),
                'per_page' => $insights->perPage(),
                'to' => $insights->lastItem(),
                'total' => $insights->total(),
            ],
        ]);
    }

    /**
     * Display the specified insight (Public)
     * 
     * @param string $slug
     * @return JsonResponse
     */
    public function show(string $slug): JsonResponse
    {
        $insight = $this->insightService->getInsightBySlug($slug);

        return response()->json([
            'success' => true,
            'message' => 'Insight retrieved successfully',
            'data' => new InsightDetailResource($insight),
        ]);
    }

    /**
     * Store a newly created insight (Auth required)
     * 
     * @param StoreInsightRequest $request
     * @return JsonResponse
     */
    public function store(StoreInsightRequest $request): JsonResponse
    {
        $this->authorize('create', Insight::class);

        $insight = $this->insightService->createInsight(
            $request->validated(),
            auth()->id()
        );

        return response()->json([
            'success' => true,
            'message' => 'Insight created successfully',
            'data' => new InsightDetailResource($insight),
        ], 201);
    }

    /**
     * Update the specified insight (Owner/Admin only)
     * 
     * @param UpdateInsightRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateInsightRequest $request, int $id): JsonResponse
    {
        $insight = Insight::findOrFail($id);
        
        $this->authorize('update', $insight);

        $updated = $this->insightService->updateInsight($insight, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Insight updated successfully',
            'data' => new InsightDetailResource($updated),
        ]);
    }

    /**
     * Remove the specified insight (Owner/Admin only)
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $insight = Insight::findOrFail($id);
        
        $this->authorize('delete', $insight);

        $this->insightService->deleteInsight($insight);

        return response()->json([
            'success' => true,
            'message' => 'Insight deleted successfully',
        ]);
    }

    /**
     * Increment view count (Public)
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function incrementView(int $id): JsonResponse
    {
        $insight = Insight::findOrFail($id);
        
        $this->insightService->incrementViewCount($insight);

        return response()->json([
            'success' => true,
            'message' => 'View count incremented',
            'data' => [
                'view_count' => $insight->fresh()->view_count,
            ],
        ]);
    }

    /**
     * Get current user's insights (Auth required)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function myInsights(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        
        $insights = $this->insightService->getUserInsights(auth()->id(), $perPage);

        return response()->json([
            'success' => true,
            'message' => 'Your insights retrieved successfully',
            'data' => InsightResource::collection($insights),
            'meta' => [
                'current_page' => $insights->currentPage(),
                'from' => $insights->firstItem(),
                'last_page' => $insights->lastPage(),
                'per_page' => $insights->perPage(),
                'to' => $insights->lastItem(),
                'total' => $insights->total(),
            ],
        ]);
    }
}