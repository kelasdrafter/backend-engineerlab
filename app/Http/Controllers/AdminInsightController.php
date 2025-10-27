<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateInsightRequest;
use App\Http\Resources\InsightResource;
use App\Http\Resources\InsightDetailResource;
use App\Models\Insight;
use App\Services\InsightService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminInsightController extends Controller
{
    protected InsightService $insightService;

    public function __construct(InsightService $insightService)
    {
        $this->insightService = $insightService;
    }

    /**
     * Display all insights for admin (Admin only)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        // Only admin can access
        $adminIds = config('insight.admin_user_ids', []);
        if (!in_array(auth()->id(), $adminIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin access required.',
            ], 403);
        }

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
            'message' => 'All insights retrieved successfully',
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
     * Update any insight (Admin only)
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
     * Delete any insight (Admin only)
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
}