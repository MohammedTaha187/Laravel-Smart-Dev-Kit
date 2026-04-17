<?php

namespace EasyDev\Laravel\Tests\Feature;

use EasyDev\Laravel\Tests\TestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SmartCrudTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock Schema to avoid PDO driver issues in some environments
        Schema::shouldReceive('getTables')->andReturn([]);
        Schema::shouldReceive('getForeignKeys')->andReturn([]);
        Schema::shouldReceive('getColumns')->andReturn([]);
        
        // Setup temporary paths for testing
        config(['starter-kit.paths.controller' => base_path('app/Http/Controllers/Api/V1')]);
        config(['starter-kit.namespaces.controller' => 'App\\Http\\Controllers\\Api\\V1']);
        
        $dirs = [
            app_path('Models'),
            app_path('Http/Controllers/Api/V1'),
            app_path('Http/Requests'),
            app_path('Http/Resources'),
            app_path('Services'),
            app_path('Repositories'),
            app_path('Contracts'),
            app_path('Data'),
            app_path('Policies'),
            base_path('tests/Feature'),
        ];

        foreach ($dirs as $dir) {
            if (!File::exists($dir)) {
                File::makeDirectory($dir, 0755, true);
            } else {
                File::cleanDirectory($dir);
            }
        }
    }

    /** @test */
    public function it_can_generate_all_professional_architecture_files()
    {
        $model = 'Product';
        
        $this->artisan('smart:crud', ['name' => $model, '--no-interaction' => true])
             ->assertSuccessful();

        // 1. Model
        $this->assertTrue(File::exists(app_path("Models/{$model}.php")));
        
        // 2. Controller
        $this->assertTrue(File::exists(app_path("Http/Controllers/Api/V1/{$model}Controller.php")));
        
        // 3. Service Interface & Implementation
        $this->assertTrue(File::exists(app_path("Contracts/{$model}ServiceInterface.php")));
        $this->assertTrue(File::exists(app_path("Services/{$model}Service.php")));
        
        // 4. Repository Interface & Implementation
        $this->assertTrue(File::exists(app_path("Contracts/{$model}RepositoryInterface.php")));
        $this->assertTrue(File::exists(app_path("Repositories/{$model}Repository.php")));
        
        // 5. DTO (Spatie Data)
        $this->assertTrue(File::exists(app_path("Data/{$model}Data.php")));
        
        // 6. Request & Resource
        $this->assertTrue(File::exists(app_path("Http/Requests/{$model}Request.php")));
        $this->assertTrue(File::exists(app_path("Http/Resources/{$model}Resource.php")));
        
        // 7. Policy
        $this->assertTrue(File::exists(app_path("Policies/{$model}Policy.php")));
        
        // 8. Test (Pest)
        $this->assertTrue(File::exists(base_path("tests/Feature/{$model}Test.php")));

        // Verify Content: Controller should use Interface
        $controllerContent = File::get(app_path("Http/Controllers/Api/V1/{$model}Controller.php"));
        $this->assertStringContainsString("{$model}ServiceInterface \${$model}Service", $controllerContent);
        $this->assertStringContainsString("{$model}RepositoryInterface \${$model}Repository", $controllerContent);
    }
}
