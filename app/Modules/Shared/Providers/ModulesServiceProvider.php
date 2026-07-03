<?php

namespace App\Modules\Shared\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ModulesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $providers = $this->discoverModuleProviders();

        foreach ($providers as $provider) {
            $this->app->register($provider);
        }
    }

    public function boot(): void
    {
        $this->loadModuleRoutes();
    }

    /**
     * @return list<class-string<ServiceProvider>>
     */
    private function discoverModuleProviders(): array
    {
        $providers = [];
        $pattern = app_path('Modules/*/Providers/*ServiceProvider.php');

        foreach (File::glob($pattern) as $file) {
            $path = is_string($file) ? $file : $file->getPathname();
            $filename = basename($path);

            if (str_contains($filename, 'ModulesServiceProvider')) {
                continue;
            }

            $relative = str_replace([app_path().DIRECTORY_SEPARATOR, '.php'], ['', ''], $path);
            $class = 'App\\'.str_replace(DIRECTORY_SEPARATOR, '\\', $relative);

            if (class_exists($class)) {
                $providers[] = $class;
            }
        }

        sort($providers);

        return $providers;
    }

    private function loadModuleRoutes(): void
    {
        $supported = config('api.supported_versions', ['v1']);

        foreach (File::glob(app_path('Modules/*/Routes/v*.php')) as $routeFile) {
            $path = is_string($routeFile) ? $routeFile : $routeFile->getPathname();

            if (! preg_match('/\/(v\d+)\.php$/', $path, $matches)) {
                continue;
            }

            $version = $matches[1];

            if (! in_array($version, $supported, true)) {
                continue;
            }

            Route::middleware('api')
                ->prefix('api/'.$version)
                ->name($version.'.')
                ->group($path);
        }
    }
}
