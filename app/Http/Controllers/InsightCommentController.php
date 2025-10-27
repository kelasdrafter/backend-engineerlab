<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Http\Resources\InsightCommentResource;
use App\Models\InsightComment;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;

class InsightCommentController extends Controller
{
    protected CommentService $commentService;

    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }

    /**
     * Display comments for an insight (Public)
     * 
     * @param int $insightId
     * @return JsonResponse
     */
    public function index(int $insightId): JsonResponse
    {
        $comments = $this->commentService->getCommentsByInsight($insightId);

        return response()->json([
            'success' => true,
            'message' => 'Comments retrieved successfully',
            'data' => InsightCommentResource::collection($comments),
        ]);
    }

    /**
     * Store a new comment (Auth required)
     * 
     * @param StoreCommentRequest $request
     * @return JsonResponse
     */
    public function store(StoreCommentRequest $request): JsonResponse
    {
        $this->authorize('create', InsightComment::class);

        $comment = $this->commentService->createComment(
            $request->validated(),
            auth()->id()
        );

        return response()->json([
            'success' => true,
            'message' => 'Comment created successfully',
            'data' => new InsightCommentResource($comment),
        ], 201);
    }

    /**
     * Update the specified comment (Owner/Admin only)
     * 
     * @param UpdateCommentRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateCommentRequest $request, int $id): JsonResponse
    {
        $comment = InsightComment::findOrFail($id);
        
        $this->authorize('update', $comment);

        $updated = $this->commentService->updateComment($comment, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Comment updated successfully',
            'data' => new InsightCommentResource($updated),
        ]);
    }

    /**
     * Remove the specified comment (Owner/Admin only)
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $comment = InsightComment::findOrFail($id);
        
        $this->authorize('delete', $comment);

        $this->commentService->deleteComment($comment);

        return response()->json([
            'success' => true,
            'message' => 'Comment deleted successfully',
        ]);
    }

    /**
     * Get mentionable users for autocomplete (Auth required)
     * 
     * @param int $insightId
     * @return JsonResponse
     */
    public function getMentionableUsers(int $insightId): JsonResponse
    {
        $users = $this->commentService->getMentionableUsers($insightId);

        return response()->json([
            'success' => true,
            'message' => 'Mentionable users retrieved successfully',
            'data' => $users,
        ]);
    }
}