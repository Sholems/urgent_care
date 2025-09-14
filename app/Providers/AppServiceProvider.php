<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use App\Models\SmtpSetting;
use App\Models\Category;
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

        if (Schema::hasTable('smtp_settings') && ($smtp = SmtpSetting::first())) {
            config([
                'mail.default' => $smtp->mailer ?? 'smtp',
                'mail.mailers.smtp.host' => $smtp->host,
                'mail.mailers.smtp.port' => $smtp->port,
                'mail.mailers.smtp.username' => $smtp->username,
                'mail.mailers.smtp.password' => $smtp->password,
                'mail.mailers.smtp.encryption' => $smtp->encryption,
                'mail.from.address' => $smtp->from_address,
                'mail.from.name' => $smtp->from_name,
            ]);
        }

        if (Schema::hasTable('categories')) {
            $categories = Category::all();  
            View::share('categories', $categories); 
        }
    }
}

