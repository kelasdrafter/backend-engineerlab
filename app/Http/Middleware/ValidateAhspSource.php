<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\RAB\AhspSource;

class ValidateAhspSource
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get AHSP source ID from route parameter
        $ahspSourceId = $request->route('ahsp_source_id') 
            ?? $request->route('id') 
            ?? $request->input('ahsp_source_id');

        // If no AHSP source ID provided, skip validation
        if (!$ahspSourceId) {
            return $next($request);
        }

        // Find AHSP source
        $ahspSource = AhspSource::find($ahspSourceId);

        // Check if AHSP source exists
        if (!$ahspSource) {
            return response()->json([
                'success' => false,
                'message' => 'AHSP Source tidak ditemukan.',
                'errors' => [
                    'ahsp_source_id' => ['AHSP Source tidak valid.']
                ]
            ], 404);
        }

        // Check if AHSP source is active
        if (!$ahspSource->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'AHSP Source tidak aktif.',
                'errors' => [
                    'ahsp_source_id' => ['AHSP Source ini sedang tidak aktif dan tidak dapat digunakan.']
                ]
            ], 422);
        }

        // Check if AHSP source is soft deleted
        if ($ahspSource->trashed()) {
            return response()->json([
                'success' => false,
                'message' => 'AHSP Source telah dihapus.',
                'errors' => [
                    'ahsp_source_id' => ['AHSP Source ini telah dihapus.']
                ]
            ], 410);
        }

        // Add AHSP source to request attributes for later use
        $request->attributes->add(['validated_ahsp_source' => $ahspSource]);

        return $next($request);
    }

    /**
     * Validate AHSP source ownership (multi-tenant check)
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\RAB\AhspSource  $ahspSource
     * @return bool
     */
    protected function validateOwnership(Request $request, AhspSource $ahspSource): bool
    {
        // If user is not authenticated, return false
        if (!auth()->check()) {
            return false;
        }

        // Check if AHSP source belongs to current user
        // You can adjust this logic based on your multi-tenant requirements
        return $ahspSource->created_by === auth()->id();
    }

    /**
     * Check if AHSP source can be used by current user
     *
     * @param  \App\Models\RAB\AhspSource  $ahspSource
     * @return bool
     */
    protected function canUseAhspSource(AhspSource $ahspSource): bool
    {
        // If not authenticated, cannot use
        if (!auth()->check()) {
            return false;
        }

        // User can use AHSP source if:
        // 1. They created it, OR
        // 2. It's a global/shared AHSP source (you can add is_global field if needed)
        return $ahspSource->created_by === auth()->id();
    }
}