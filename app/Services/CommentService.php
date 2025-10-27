<?php

namespace App\Services;

use App\Models\InsightComment;
use App\Models\InsightCommentMedia;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str; // ✅ TAMBAHAN

class CommentService
{
    /**
     * Get comments by insight with replies
     */
    public function getCommentsByInsight(int $insightId): Collection
    {
        return InsightComment::with([
            'user:id,name,photo_url',
            'mentionedUsers:id,name',
            'media',
            'replies' => function ($query) {
                $query->with(['user:id,name,photo_url', 'mentionedUsers:id,name', 'media'])
                      ->latest();
            }
        ])
        ->where('insight_id', $insightId)
        ->whereNull('parent_id')
        ->oldest()
        ->get();
    }

    /**
     * Create new comment or reply
     */
    public function createComment(array $data, int $userId): InsightComment
    {
        $comment = InsightComment::create([
            'insight_id' => $data['insight_id'],
            'user_id' => $userId,
            'parent_id' => $data['parent_id'] ?? null,
            'comment' => $data['comment'],
        ]);

        // Handle mentions if provided
        if (!empty($data['mentioned_user_ids'])) {
            app(MentionService::class)->processMentions($comment, $data['mentioned_user_ids']);
        }

        // Handle media uploads if provided
        if (!empty($data['media']) && is_array($data['media'])) {
            $this->handleMediaUploads($comment, $data['media']);
        }

        return $comment->load(['user', 'mentionedUsers', 'replies', 'media']);
    }

    /**
     * Update comment
     */
    public function updateComment(InsightComment $comment, array $data): InsightComment
    {
        $comment->update([
            'comment' => $data['comment'],
        ]);

        if (isset($data['mentioned_user_ids'])) {
            app(MentionService::class)->updateMentions($comment, $data['mentioned_user_ids']);
        }

        if (!empty($data['media']) && is_array($data['media'])) {
            $this->handleMediaUploads($comment, $data['media']);
        }

        return $comment->fresh(['user', 'mentionedUsers', 'replies', 'media']);
    }

    /**
     * Delete comment
     */
    public function deleteComment(InsightComment $comment): bool
    {
        // ✅ PERBAIKAN: Delete media files from S3 (bukan public)
        foreach ($comment->media as $media) {
            Storage::disk('s3')->delete($media->file_path);
        }
        
        return $comment->delete();
    }

    /**
     * Get mentionable users for autocomplete
     */
    public function getMentionableUsers(int $insightId): Collection
    {
        $insight = \App\Models\Insight::with(['user', 'comments.user'])->findOrFail($insightId);
        
        $users = collect([$insight->user]);
        $commenters = $insight->comments->pluck('user')->unique('id');
        $users = $users->merge($commenters)->unique('id');
        
        return $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'photo_url' => $user->photo_url ?? null,
            ];
        })->values();
    }

    /**
     * ✅ PERBAIKAN: Handle media file uploads ke S3 (sama seperti InsightService)
     */
    protected function handleMediaUploads(InsightComment $comment, array $files): void
    {
        $order = InsightCommentMedia::where('comment_id', $comment->id)->max('order') ?? 0;

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }

            $order++;
            
            // ✅ SAMA SEPERTI InsightService: Generate unique filename
            $extension = $file->getClientOriginalExtension();
            $fileName = Str::random(40) . '.' . $extension;
            $mimeType = $file->getMimeType();
            
            $type = $this->determineMediaType($mimeType);
            
            // ✅ SAMA SEPERTI InsightService: Upload to S3 dengan putFileAs
            $path = Storage::disk('s3')->putFileAs(
                'insight-comments/media',
                $file,
                $fileName,
                'public'
            );

            InsightCommentMedia::create([
                'comment_id' => $comment->id,
                'type' => $type,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'mime_type' => $mimeType,
                'order' => $order,
            ]);
        }
    }

    /**
     * ✅ PERBAIKAN: Determine media type (sama seperti InsightService)
     */
    protected function determineMediaType(string $mimeType): string
    {
        if (Str::startsWith($mimeType, 'image/')) {
            return 'image';
        } elseif (Str::startsWith($mimeType, 'video/')) {
            return 'video';
        } else {
            return 'file';
        }
    }
}