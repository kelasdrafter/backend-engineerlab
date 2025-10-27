<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLearnCornerRequest;
use App\Http\Requests\UpdateLearnCornerRequest;
use App\Models\LearnCorner;
use App\Models\File;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class LearnCornerController extends Controller
{
    /**
     * Display a listing of the resource (Public - for users)
     * GET /api/learn-corner
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 12);
        
        $videos = QueryBuilder::for(LearnCorner::class)
            ->allowedFilters([
                AllowedFilter::partial('level'),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->search($value);
                }),
                AllowedFilter::exact('is_active'),
            ])
            ->allowedSorts(['created_at', 'view_count', 'title'])
            ->where('is_active', true)
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $videos->items(),
            'meta' => [
                'current_page' => $videos->currentPage(),
                'last_page' => $videos->lastPage(),
                'per_page' => $videos->perPage(),
                'total' => $videos->total(),
            ],
        ]);
    }

    /**
     * Display a listing of the resource (Admin - all videos)
     * GET /api/admin/learn-corner
     */
    public function adminIndex(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 15);
        
        $videos = QueryBuilder::for(LearnCorner::class)
            ->allowedFilters([
                AllowedFilter::partial('level'),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->search($value);
                }),
                AllowedFilter::exact('is_active'),
            ])
            ->allowedSorts(['created_at', 'view_count', 'title'])
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $videos->items(),
            'meta' => [
                'current_page' => $videos->currentPage(),
                'last_page' => $videos->lastPage(),
                'per_page' => $videos->perPage(),
                'total' => $videos->total(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * POST /api/admin/learn-corner
     */
    public function store(StoreLearnCornerRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            
            $learnCorner = LearnCorner::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Video berhasil dibuat',
                'data' => $learnCorner,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat video',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource (Public)
     * GET /api/learn-corner/{slug}
     */
    public function show(string $slug): JsonResponse
    {
        try {
            $video = LearnCorner::where('slug', $slug)
                ->where('is_active', true)
                ->firstOrFail();

            // Increment view count
            $video->incrementViewCount();

            // Get related videos (same level, exclude current video)
            $relatedVideos = LearnCorner::where('level', $video->level)
                ->where('id', '!=', $video->id)
                ->where('is_active', true)
                ->latest()
                ->limit(4)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $video,
                'related_videos' => $relatedVideos,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Video tidak ditemukan',
            ], 404);
        }
    }

    /**
     * Display the specified resource (Admin)
     * GET /api/admin/learn-corner/{id}
     */
    public function adminShow(string $id): JsonResponse
    {
        try {
            $video = LearnCorner::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $video,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Video tidak ditemukan',
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     * PUT /api/admin/learn-corner/{id}
     */
    public function update(UpdateLearnCornerRequest $request, string $id): JsonResponse
    {
        try {
            $video = LearnCorner::findOrFail($id);
            
            $data = $request->validated();
            
            // Hapus thumbnail lama jika ada yang baru
            if ($request->has('thumbnail_url') && $request->thumbnail_url != $video->thumbnail_url) {
                $oldUrl = $video->thumbnail_url;
                
                if ($oldUrl) {
                    try {
                        $baseUrl = rtrim(env('AWS_URL'), '/');
                        $key = ltrim(str_replace($baseUrl, '', $oldUrl), '/');
                        
                        File::deleteByPath($key);
                        
                        \Log::info('🗑️ Old thumbnail deleted', [
                            'video_id' => $id,
                            'old_url' => $oldUrl,
                            'key' => $key
                        ]);
                    } catch (\Exception $e) {
                        \Log::warning('⚠️ Failed to delete old thumbnail', [
                            'video_id' => $id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
            
            $video->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Video berhasil diperbarui',
                'data' => $video,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui video',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     * DELETE /api/admin/learn-corner/{id}
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $video = LearnCorner::findOrFail($id);
            
            // Hapus thumbnail dari S3 sebelum delete record
            if ($video->thumbnail_url) {
                try {
                    $url = $video->thumbnail_url;
                    $baseUrl = rtrim(env('AWS_URL'), '/');
                    $key = ltrim(str_replace($baseUrl, '', $url), '/');
                    
                    File::deleteByPath($key);
                    
                    \Log::info('🗑️ Thumbnail deleted on video deletion', [
                        'video_id' => $id,
                        'url' => $url,
                        'key' => $key
                    ]);
                } catch (\Exception $e) {
                    \Log::warning('⚠️ Failed to delete thumbnail on video deletion', [
                        'video_id' => $id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // Update slug sebelum soft delete
            $newSlug = $video->slug . '-deleted-' . time();
            LearnCorner::where('id', $video->id)->update(['slug' => $newSlug]);
            
            $video->delete();

            return response()->json([
                'success' => true,
                'message' => 'Video berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus video',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle active status
     * PATCH /api/admin/learn-corner/{id}/toggle-active
     */
    public function toggleActive(string $id): JsonResponse
    {
        try {
            $video = LearnCorner::findOrFail($id);
            $video->is_active = !$video->is_active;
            $video->save();

            return response()->json([
                'success' => true,
                'message' => 'Status video berhasil diubah',
                'data' => $video,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status video',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get statistics (Admin)
     * GET /api/admin/learn-corner/statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = [
            'total_videos' => LearnCorner::count(),
            'active_videos' => LearnCorner::where('is_active', true)->count(),
            'inactive_videos' => LearnCorner::where('is_active', false)->count(),
            'total_views' => LearnCorner::sum('view_count'),
            'most_popular' => LearnCorner::where('is_active', true)
                ->orderBy('view_count', 'desc')
                ->limit(5)
                ->get(),
            'levels' => LearnCorner::where('is_active', true)
                ->select('level')
                ->groupBy('level')
                ->selectRaw('level, count(*) as count')
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

/**
 * Upload custom thumbnail to S3 (folder learn-corner/)
 * POST /api/admin/learn-corner/upload-thumbnail
 */
public function uploadThumbnail(Request $request): JsonResponse
{
    try {
        // Validate request
        $request->validate([
            'thumbnail' => [
                'required',
                'file',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:5120' // 5MB
            ]
        ]);

        if (!$request->hasFile('thumbnail')) {
            return response()->json([
                'success' => false,
                'message' => 'No file uploaded'
            ], 400);
        }

        $file = $request->file('thumbnail');

        // ✅ Generate unique filename dengan folder learn-corner/
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid() . '.' . $extension;
        $path = 'learn-corner/' . $filename;

        \Log::info('📤 Uploading thumbnail', [
            'original_name' => $file->getClientOriginalName(),
            'filename' => $filename,
            'path' => $path,
            'size' => $file->getSize(),
            'mime' => $file->getMimeType()
        ]);

        // ✅ PERBAIKAN: Gunakan putFileAs() yang return path string
        $uploadedPath = Storage::disk('s3')->putFileAs(
            'learn-corner',           // Folder
            $file,                    // File object
            $filename,                // Nama file
            'public'                  // Visibility
        );

        if (!$uploadedPath) {
            throw new \Exception('Failed to upload file to S3');
        }

        // ✅ Generate full URL secara manual (lebih reliable)
        $baseUrl = rtrim(env('AWS_URL'), '/');
        $url = $baseUrl . '/' . $uploadedPath;

        \Log::info('✅ Thumbnail uploaded successfully', [
            'uploaded_path' => $uploadedPath,
            'url' => $url,
            'base_url' => $baseUrl
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thumbnail uploaded successfully',
            'data' => [
                'url' => $url,
                'path' => $uploadedPath,
                'filename' => $filename
            ]
        ], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {
        \Log::error('❌ Validation failed', ['errors' => $e->errors()]);
        
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $e->errors()
        ], 422);

    } catch (\Exception $e) {
        \Log::error('❌ Thumbnail upload error', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Upload failed: ' . $e->getMessage()
        ], 500);
    }
}
}