<?php

namespace App\Observers;

use App\Models\Insight;
use App\Services\PointService;

class InsightObserver
{
    /**
     * Handle the Insight "created" event.
     * 
     * Automatically triggered when Insight::create() is called.
     * Updates user points and rank.
     */
    public function created(Insight $insight): void
    {
        app(PointService::class)->handleInsightCreated($insight->user_id);
    }

    /**
     * Handle the Insight "deleted" event.
     * 
     * Automatically triggered when $insight->delete() is called.
     * Recalculates user points and rank after insight deletion.
     */
    public function deleted(Insight $insight): void
    {
        app(PointService::class)->handleInsightDeleted($insight->user_id);
    }
}