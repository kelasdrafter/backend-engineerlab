<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

// ✅ Import Policies
use App\Policies\InsightPolicy;
use App\Policies\InsightCommentPolicy;
use App\Policies\InsightCategoryPolicy;
use App\Policies\AhspSourcePolicy;
use App\Policies\MasterAhspPolicy;

// ✅ Import Models
use App\Models\Insight;
use App\Models\InsightComment;
use App\Models\InsightCategory;
use App\Models\RAB\AhspSource;
use App\Models\RAB\MasterAhsp;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // ✅ Insight Lab Policies
        Insight::class => InsightPolicy::class,
        InsightComment::class => InsightCommentPolicy::class,
        InsightCategory::class => InsightCategoryPolicy::class,
        
        // ✅ RAB Policies
        AhspSource::class => AhspSourcePolicy::class,
        MasterAhsp::class => MasterAhspPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Custom password reset link, remove this if you want to use the default link
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            $url = "/password-reset?token=$token&email={$notifiable->getEmailForPasswordReset()}";

            return config('app.frontend_url') . $url;
        });

        // Custom verify email link (you should do a GET request to the $url), remove this if you want to use the default link
        VerifyEmail::createUrlUsing(function (object $notifiable) {
            $url = URL::temporarySignedRoute(
                'verification.verify',
                Carbon::now()->addMinutes(config('auth.verification.expire', 60)),
                [
                    'id' => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification())
                ]
            );

            $needle = config('app.url') . '/api/verify-email/';
            $targetUrl = str_replace($needle, '', urldecode($url));

            return config('app.frontend_url') . '/verify-email?url=' . $targetUrl;
        });
    }
}