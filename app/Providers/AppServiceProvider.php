<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\View\Components\SelectInput;
use App\View\Components\FileInput;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register()
    {
        $this->app->singleton(PubblicazioneService::class, function ($app) {
            return new PubblicazioneService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::component('select-input', SelectInput::class);
        Blade::component('file-input', FileInput::class);
        $this->loadRoutesFrom(base_path('routes/schedules/pubblicazioni.php'));
    }
}
