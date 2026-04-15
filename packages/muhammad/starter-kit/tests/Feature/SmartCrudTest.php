<?php

namespace Muhammad\StarterKit\Tests\Feature;

use Muhammad\StarterKit\Tests\TestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class SmartCrudTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup temporary paths for testing
        config(['starter-kit.paths.controller' => base_path('app/Http/Controllers/Api/V1')]);
        
        if (!File::exists(app_path('Models'))) {
            File::makeDirectory(app_path('Models'), 0755, true);
        }
    }

    /** @test */
    public function it_can_generate_crud_files()
    {
        $model = 'TestModel';
        
        Artisan::call('smart:crud', ['name' => $model, '--no-interaction' => true]);

        $this->assertTrue(File::exists(app_path("Models/{$model}.php")));
        $this->assertTrue(File::exists(app_path("Http/Controllers/Api/V1/{$model}Controller.php")));
        
        // Cleanup
        File::delete(app_path("Models/{$model}.php"));
        File::delete(app_path("Http/Controllers/Api/V1/{$model}Controller.php"));
    }
}
