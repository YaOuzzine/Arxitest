<?php

namespace App\Providers;

use App\Http\View\Composers\DashboardLayoutComposer;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Laravel\Passport\Passport;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Microsoft\Provider;
use SocialiteProviders\Atlassian\AtlassianExtendSocialite;
use App\Http\Middleware\CheckGitHubConnected;
use App\Services\AI\AIGenerationService;
use App\Services\GitHubApiClient;
use SocialiteProviders\GitHub\GitHubExtendSocialite;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AIGenerationService::class, function ($app) {
            return new AIGenerationService();
        });

        $this->app->singleton(GitHubApiClient::class, function ($app) {
            return new \App\Services\GitHubApiClient();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(function (SocialiteWasCalled $event) {
            $event->extendSocialite('microsoft', Provider::class);
            // Update this line to use the correct extender
            $event->extendSocialite('github', GitHubExtendSocialite::class);
        });

        View::composer('layouts.dashboard', DashboardLayoutComposer::class);
    }
}
