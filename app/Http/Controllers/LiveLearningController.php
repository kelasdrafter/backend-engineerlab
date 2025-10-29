<?php

namespace App\Http\Controllers;

use App\Http\Resources\LiveLearningResource;
use App\Models\LiveLearning;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LiveLearningController extends Controller
{
    /**
     * Display a listing of live learnings (Public)
     * GET /api/live-learnings
     * 
     * Supports filtering, sorting, pagination
     */
    public function index(Request $request)
    {
        try {
            $query = LiveLearning::query()
                ->with('registrations') // For count
                ->withCount('registrations')
                ->published(); // Only show published

            // Filter by is_paid
            if ($request->has('filter.is_paid')) {
                $isPaid = filter_var($request->input('filter.is_paid'), FILTER_VALIDATE_BOOLEAN);
                $query->where('is_paid', $isPaid);
            }

            // Filter by title (search)
            if ($request->has('filter.title')) {
                $query->where('title', 'like', '%' . $request->input('filter.title') . '%');
            }

            // Sorting
            $sortField = $request->input('sort', '-created_at'); // Default: newest first
            $sortDirection = 'asc';
            
            if (Str::startsWith($sortField, '-')) {
                $sortDirection = 'desc';
                $sortField = Str::substr($sortField, 1);
            }

            // Allowed sort fields
            $allowedSortFields = ['created_at', 'updated_at', 'title', 'schedule'];
            if (in_array($sortField, $allowedSortFields)) {
                $query->orderBy($sortField, $sortDirection);
            }

            // Pagination
            $perPage = $request->input('per_page', 15);
            $liveLearnings = $query->paginate($perPage);

            return LiveLearningResource::collection($liveLearnings);

        } catch (\Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }

    /**
     * Display a single live learning by slug (Public)
     * GET /api/live-learnings/{slug}
     */
    public function show($slug)
    {
        try {
            $liveLearning = LiveLearning::where('slug', $slug)
                ->published()
                ->withCount('registrations')
                ->firstOrFail();

            return new LiveLearningResource($liveLearning);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'meta' => [
                    'message' => 'Live Learning tidak ditemukan',
                    'code' => 404,
                ],
            ], 404);
        } catch (\Exception $exception) {
            return $this->responseError($exception, [], 500);
        }
    }
}