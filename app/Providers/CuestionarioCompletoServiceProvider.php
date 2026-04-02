<?php

namespace App\Providers;

use App\Services\CuestionarioCompletoService;
use App\Services\FormConditionEvaluator;
use Illuminate\Support\ServiceProvider;

class CuestionarioCompletoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CuestionarioCompletoService::class, function ($app) {
            return new CuestionarioCompletoService(
                $app->make(FormConditionEvaluator::class)
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
