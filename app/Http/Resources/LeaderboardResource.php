<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaderboardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->user->id,                    // 👈 FLATTEN STRUCTURE
            'name' => $this->user->name,                // 👈 FLATTEN STRUCTURE  
            'avatar' => $this->user->photo_url ?? null, // 👈 UBAH JADI 'avatar'
            'insights_count' => $this->insight_count,   // 👈 SESUAI INTERFACE
            'total_points' => $this->total_points,
            'rank_position' => $this->resource->rank_position ?? null,
            'occupation' => $this->user->occupation ?? null,
            'current_rank' => $this->currentRank ? [
                'id' => $this->currentRank->id,
                'name' => $this->currentRank->name,
                'slug' => $this->currentRank->slug,
                'icon' => $this->currentRank->icon,
                'min_points' => $this->currentRank->min_points,
                'max_points' => $this->currentRank->max_points,
            ] : null,
        ];
    }

    /**
     * Additional data for collection response
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'leaderboard_limit' => 4,
            ],
        ];
    }
}