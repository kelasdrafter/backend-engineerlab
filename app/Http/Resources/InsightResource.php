<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\InsightUserProfile;

class InsightResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'content_preview' => $this->getContentPreview(),
            'view_count' => $this->view_count,
            'comment_count' => $this->comment_count,
            'created_at' => $this->created_at?->toIso8601String(),
            'user_id' => $this->user_id,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'avatar' => $this->user->photo_url ?? null,
                'rank' => $this->getUserRank(), // 👈 TAMBAH BARIS INI
            ],
            'category' => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
                'icon' => $this->category->icon,
            ],
        ];
    }

    /**
     * Get content preview (first 200 characters)
     */
    private function getContentPreview(): string
    {
        $content = strip_tags($this->content);
        
        if (mb_strlen($content) > 200) {
            return mb_substr($content, 0, 200) . '...';
        }
        
        return $content;
    }

    /**
     * Get user rank from profile
     */
    private function getUserRank(): array
    {
        $profile = InsightUserProfile::with('currentRank')
            ->where('user_id', $this->user->id)
            ->first();
        
        if ($profile && $profile->currentRank) {
            return [
                'name' => $profile->currentRank->name,
                'icon' => $profile->currentRank->icon,
                'slug' => $profile->currentRank->slug,
            ];
        }
        
        // Fallback berdasarkan jumlah insights
        $insightCount = \App\Models\Insight::where('user_id', $this->user->id)->count();
        
        if ($insightCount >= 10) {
            return ['name' => 'Expert', 'icon' => '🥇', 'slug' => 'expert'];
        } elseif ($insightCount >= 5) {
            return ['name' => 'Contributor', 'icon' => '🥈', 'slug' => 'contributor'];
        } else {
            return ['name' => 'Newbie', 'icon' => '🥉', 'slug' => 'newbie'];
        }
    }
}