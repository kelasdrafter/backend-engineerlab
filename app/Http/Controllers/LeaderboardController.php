<?php

namespace App\Http\Controllers;

use App\Http\Resources\LeaderboardResource;
use App\Models\InsightUserProfile;
use Illuminate\Http\JsonResponse;

class LeaderboardController extends Controller
{
    /**
     * Display top 4 users (Public)
     * 
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $topUsers = InsightUserProfile::with(['user', 'currentRank'])
            ->orderBy('total_points', 'desc')
            ->limit(4)
            ->get()
            ->map(function ($profile, $index) {
                $profile->rank_position = $index + 1;
                return $profile;
            });

        return response()->json([
            'success' => true,
            'message' => 'Leaderboard retrieved successfully',
            'data' => LeaderboardResource::collection($topUsers),
        ]);
    }
}