<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use App\Services\SmtpConfigurator;
use App\Models\Category;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        if (Schema::hasTable('smtp_settings')) {
            SmtpConfigurator::apply();
        }

        if (Schema::hasTable('categories')) {
            $categories = Category::all();
            View::share('categories', $categories);
        }

        $siteSetting = null;
        if (Schema::hasTable('site_settings')) {
            $siteSetting = SiteSetting::first();
        }

        View::share('siteSetting', $siteSetting);
    }
}
