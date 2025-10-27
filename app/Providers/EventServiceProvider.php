<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

// ✅ Import Observers
use App\Observers\InsightObserver;
use App\Observers\InsightCommentObserver;
use App\Observers\InsightMediaObserver;

// ✅ Import Models
use App\Models\Insight;
use App\Models\InsightComment;
use App\Models\InsightMedia;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * The model observers for the application.
     * 
     * ✅ NEW: Observer mappings for Insight Lab feature
     * Observers handle automatic side effects when models are created/deleted.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $observers = [
        Insight::class => [InsightObserver::class],
        InsightComment::class => [InsightCommentObserver::class],
        InsightMedia::class => [InsightMediaObserver::class],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}