<?php

namespace App\Observers;

use App\Models\InsightMedia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class InsightMediaObserver
{
    /**
     * Handle the InsightMedia "deleted" event.
     * 
     * Automatically triggered when $media->delete() is called.
     * Deletes the file from S3 storage.
     * 
     * This replaces the old booted() method in InsightMedia model.
     */
    public function deleted(InsightMedia $media): void
    {
        try {
            // Check if file exists in S3
            if (Storage::disk('s3')->exists($media->file_path)) {
                // Delete file from S3
                Storage::disk('s3')->delete($media->file_path);
                
                Log::info('S3 file deleted', [
                    'file_path' => $media->file_path,
                    'media_id' => $media->id,
                ]);
            }
        } catch (\Exception $e) {
            // Log error but don't throw exception
            // Allow deletion to continue even if S3 delete fails
            Log::error('Failed to delete S3 file', [
                'file_path' => $media->file_path,
                'media_id' => $media->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}