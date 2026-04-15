<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class BindingServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->bindInterfaces(app_path('Services'), 'App\\Services');
        $this->bindInterfaces(app_path('Repositories'), 'App\\Repositories');
        
        // Scan Modules
        if (File::exists(base_path('Modules'))) {
            foreach (File::directories(base_path('Modules')) as $modulePath) {
                $moduleName = basename($modulePath);
                $this->bindInterfaces("{$modulePath}/app/Services", "Modules\\{$moduleName}\\Services");
                $this->bindInterfaces("{$modulePath}/app/Repositories", "Modules\\{$moduleName}\\Repositories");
            }
        }
    }

    protected function bindInterfaces($path, $namespace)
    {
        if (! File::exists($path)) return;

        foreach (File::files($path) as $file) {
            $class = $namespace . '\\' . $file->getBasename('.php');
            $interface = $namespace . '\\' . $file->getBasename('.php') . 'Interface';

            // Check if interface exists in Contracts
            $contractNamespace = str_replace(['Services', 'Repositories'], 'Contracts', $namespace);
            $interface = $contractNamespace . '\\' . $file->getBasename('.php') . 'Interface';

            if (interface_exists($interface)) {
                $this->app->bind($interface, $class);
            }
        }
    }
}
