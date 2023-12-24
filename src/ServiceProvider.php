<?php

namespace Jalno\UserLogger;

use Jalno\UserLogger\Contracts\ILog;
use Jalno\UserLogger\Contracts\ILogger;
use Jalno\UserLogger\Policies\LogPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider as SupportServiceProvider;

class ServiceProvider extends SupportServiceProvider
{
    /**
     * @var array<class-string,class-string>
     */
    protected $policies = [
        ILog::class => LogPolicy::class,
    ];

    public function register()
    {
        if (!app()->configurationIsCached()) {
            $this->mergeConfigFrom(__DIR__ . '/../config/jalno-user-logger.php', 'jalno-user-logger');
            if (config('jalno-user-logger.routes.enable')) {
                config(['user-logger.routes' => array_merge(
                    config('user-logger.routes', []), [
                        'enable' => false,
                    ]),
                ]);
            }
        }
        $this->app->bind(ILogger::class, Logger::class);
        $this->app->bind(\dnj\UserLogger\Contracts\ILogger::class, Logger::class);
    }

    /**
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        $this->registerRoutes();
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/jalno-user-logger.php' => config_path('jalno-user-logger.php'),
            ], 'config');
        }
    }

    protected function registerRoutes(): void
    {
        if (app()->routesAreCached() or !config('jalno-user-logger.routes.enable')) {
            return;
        }
        /** @var string $prefix */
        $prefix = config('jalno-user-logger.routes.prefix', 'api/jalno-user-logger');
        Route::prefix($prefix)->name('jalno-user-logger.')->group(function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        });
    }

    protected function registerPolicies(): void
    {
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }
}
