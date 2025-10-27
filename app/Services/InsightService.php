<?php

namespace App\Services;

use App\Models\Insight;
use App\Models\InsightMedia;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InsightService
{
    /**
     * Get all insights with filters and pagination
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllInsights(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Insight::with(['user:id,name,photo_url', 'category:id,name,slug,icon']);

        // Filter by category
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // Filter by category slug
        if (!empty($filters['category_slug'])) {
            $query->whereHas('category', function (Builder $q) use ($filters) {
                $q->where('slug', $filters['category_slug']);
            });
        }

        // Filter by user
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        // Search by title or content
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function (Builder $q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('content', 'LIKE', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        
        $allowedSorts = ['created_at', 'view_count', 'comment_count'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->latest();
        }

        return $query->paginate($perPage);
    }

    /**
     * Get insight by slug
     *
     * @param string $slug
     * @return Insight
     */
    public function getInsightBySlug(string $slug): Insight
    {
        return Insight::with([
            'user:id,name,photo_url,occupation',
            'category:id,name,slug,icon,description',
            'media' => function ($query) {
                $query->orderBy('type')->orderBy('order');
            }
        ])
        ->where('slug', $slug)
        ->firstOrFail();
    }

    /**
     * Create new insight
     *
     * @param array $data
     * @param int $userId
     * @return Insight
     */
    public function createInsight(array $data, int $userId): Insight
    {
        $insight = Insight::create([
            'user_id' => $userId,
            'category_id' => $data['category_id'],
            'title' => $data['title'],
            'content' => $data['content'],
        ]);

        // Observer will automatically handle point update
        // via InsightObserver->created()

        // ✅ TAMBAHAN: Upload media files to S3
        if (isset($data['media']) && is_array($data['media'])) {
            $this->uploadMediaFiles($data['media'], $insight->id);
        }

        return $insight->load(['user', 'category', 'media']);
    }

    /**
     * Update insight
     *
     * @param Insight $insight
     * @param array $data
     * @return Insight
     */
    public function updateInsight(Insight $insight, array $data): Insight
    {
        $insight->update(array_filter([
            'category_id' => $data['category_id'] ?? null,
            'title' => $data['title'] ?? null,
            'content' => $data['content'] ?? null,
        ]));

        // ✅ TAMBAHAN: Delete old media if requested
        if (isset($data['delete_media_ids']) && is_array($data['delete_media_ids'])) {
            InsightMedia::whereIn('id', $data['delete_media_ids'])
                ->where('insight_id', $insight->id)
                ->delete(); // Observer akan handle delete S3 file
        }

        // ✅ TAMBAHAN: Upload new media files
        if (isset($data['media']) && is_array($data['media'])) {
            $this->uploadMediaFiles($data['media'], $insight->id);
        }

        return $insight->fresh(['user', 'category', 'media']);
    }

    /**
     * Delete insight
     *
     * @param Insight $insight
     * @return bool
     */
    public function deleteInsight(Insight $insight): bool
    {
        // Observer will automatically handle:
        // - Point recalculation (InsightObserver->deleted)
        // - Media deletion (InsightMediaObserver->deleted for each media)
        // - Comment deletion (cascade + InsightCommentObserver->deleted for each)
        
        return $insight->delete();
    }

    /**
     * Increment view count
     *
     * @param Insight $insight
     * @return void
     */
    public function incrementViewCount(Insight $insight): void
    {
        $insight->incrementViewCount();
    }

    /**
     * Get user's insights
     *
     * @param int $userId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getUserInsights(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Insight::with(['category:id,name,slug,icon'])
            ->where('user_id', $userId)
            ->latest()
            ->paginate($perPage);
    }

    // ========================================
    // ✅ METHOD BARU - UNTUK UPLOAD MEDIA
    // ========================================

    /**
     * 📤 Upload media files to S3
     *
     * @param array $files Array of UploadedFile objects
     * @param int $insightId
     * @return void
     */
    private function uploadMediaFiles(array $files, int $insightId): void
    {
        $order = InsightMedia::where('insight_id', $insightId)->max('order') ?? 0;

        foreach ($files as $file) {
            $order++;
            
            // Generate unique filename
            $extension = $file->getClientOriginalExtension();
            $fileName = Str::random(40) . '.' . $extension;
            $mimeType = $file->getMimeType();
            
            // Determine media type (image/video/file)
            $type = $this->determineMediaType($mimeType);
            
            // Upload to S3
            $path = Storage::disk('s3')->putFileAs(
                'insights/media',
                $file,
                $fileName,
                'public'
            );

            // Save to database
            InsightMedia::create([
                'insight_id' => $insightId,
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
     * 🎯 Determine media type from MIME type
     *
     * @param string $mimeType
     * @return string
     */
    private function determineMediaType(string $mimeType): string
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