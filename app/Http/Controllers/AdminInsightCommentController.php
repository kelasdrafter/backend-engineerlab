<?php

namespace App\Http\Controllers;

use App\Models\InsightComment;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;

class AdminInsightCommentController extends Controller
{
    protected CommentService $commentService;

    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }

    /**
     * Delete any comment (Admin only)
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
}