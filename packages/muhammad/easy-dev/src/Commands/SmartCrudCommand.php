<?php

namespace EasyDev\Laravel\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use EasyDev\Laravel\Services\DBAnalyzer;

class SmartCrudCommand extends Command
{
    protected $signature = 'smart:crud {name}
                            {--api : Create an API controller}
                            {--with-service : Create a service class}
                            {--with-repository : Create a repository class}
                            {--with-resource : Create an API resource}
                            {--with-data : Create a Spatie Data object (Type-Safe)}
                            {--with-contracts : Generate interfaces/contracts for services and repositories}
                            {--module= : Generate code inside a specific module}
                            {--with-policy : Create a policy}
                            {--with-tests : Create Pest tests}
                            {--with-media : Add media support using Spatie Media Library}
                            {--with-event : Generate model events}
                            {--with-notification : Generate a default notification class}
                            {--with-logs : Add activity logging}
                            {--soft-delete : Add soft delete support}
                            {--translatable= : Comma-separated list of translatable fields}';

    protected $description = 'Generate a complete CRUD feature set (11 files)';

    protected string $model;
    protected ?string $module;
    protected array $config;
    protected DBAnalyzer $analyzer;

    public function __construct(DBAnalyzer $analyzer)
    {
        parent::__construct();
        $this->analyzer = $analyzer;
    }

    public function handle(): void
    {
        $this->model  = $this->argument('name');
        $this->module = $this->option('module');
        $this->config = config('starter-kit');

        $this->info("🚀 Generating CRUD for [{$this->model}]" . ($this->module ? " in module [{$this->module}]" : '') . '...');
        $this->newLine();

        if ($this->module) {
            $this->initializeModule();
        }

        // Layer 1 — Model + Migration
        $this->generateModel();
        $this->generateMigration();

        // Layer 2 — HTTP
        $this->generateStoreRequest();
        $this->generateUpdateRequest();
        $this->generateResource();
        $this->generateCollection();

        // Layer 3 — Service
        $this->generateService();

        // Layer 4 — Repository
        $this->generateRepository();

        // Layer 5 — DTO + Policy + Test
        $this->generateData();
        $this->generatePolicy();
        $this->generateFactory();
        $this->generateTest();

        // Controller (wires everything together)
        $this->generateController();

        // Optional extras
        if ($this->option('with-notification')) {
            $this->generateNotification();
        }
        if ($this->option('with-event')) {
            $this->generateEvent();
        }

        // Route registration
        $this->registerRoute();

        $this->newLine();
        $this->info("✅ CRUD for [{$this->model}] generated successfully!");
        $this->newLine();
        $this->warn('⚡ Remember to:');
        $this->line('  1. Add bindings to RepositoryServiceProvider');
        $this->line('  2. Run: php artisan migrate');
        $this->line('  3. Grant permissions for the new model policies');
    }

    // ──────────────────────────────────────────────────
    // Path & Namespace Helpers
    // ──────────────────────────────────────────────────

    protected function getBasePath(): string
    {
        return $this->module
            ? base_path("Modules/{$this->module}")
            : app_path();
    }

    protected function getBaseNamespace(): string
    {
        return $this->module
            ? "Modules\\{$this->module}"
            : 'App';
    }

    /**
     * Api/V1/{Module} sub-path for controllers, requests, resources.
     */
    protected function getApiSubPath(): string
    {
        return $this->module ? "Api/V1/{$this->module}" : 'Api/V1';
    }

    protected function getApiSubNamespace(): string
    {
        return $this->module ? "Api\\V1\\{$this->module}" : 'Api\\V1';
    }

    // ──────────────────────────────────────────────────
    // Layer 1 — Model + Migration
    // ──────────────────────────────────────────────────

    protected function generateModel(): void
    {
        $basePath = $this->getBasePath();
        $path     = "{$basePath}/Models/{$this->model}.php";
        $stub     = File::get(__DIR__ . '/../../stubs/model.stub');

        $namespace = $this->getBaseNamespace() . '\\Models';

        $table     = Str::snake(Str::pluralStudly($this->model));
        $columns   = array_column($this->analyzer->getColumns($table), 'name');
        $fillable  = array_filter($columns, function($c) {
            return ! in_array($c, ['id', 'created_at', 'updated_at', 'deleted_at']);
        });
        $fillableString = "'" . implode("', '", $fillable) . "'";

        $replacements = [
            '{{Namespace}}'         => $namespace,
            '{{Class}}'             => $this->model,
            '{{Fillable}}'          => $fillableString,
            '{{SoftDeletesImport}}' => $this->option('soft-delete')
                ? 'use Illuminate\\Database\\Eloquent\\SoftDeletes;'
                : '',
            '{{SoftDeletesTrait}}'  => $this->option('soft-delete')
                ? ', SoftDeletes'
                : '',
            '{{MediaImports}}'      => $this->option('with-media')
                ? "use Spatie\\MediaLibrary\\HasMedia;\nuse Spatie\\MediaLibrary\\InteractsWithMedia;"
                : '',
            '{{MediaImplements}}'   => $this->option('with-media') ? 'implements HasMedia' : '',
            '{{MediaTrait}}'        => $this->option('with-media') ? ', InteractsWithMedia' : '',
            '{{TranslationImport}}' => $this->option('translatable')
                ? 'use Spatie\\Translatable\\HasTranslations;'
                : '',
            '{{TranslationTrait}}'  => $this->option('translatable') ? ', HasTranslations' : '',
            '{{TranslatableFields}}' => $this->getTranslatableFields(),
            '{{EnterpriseTraits}}'  => $this->getEnterpriseTraits(),
            '{{FactoryMethod}}'     => $this->getFactoryMethod($namespace),
        ];

        $this->createFile($path, $stub, $replacements);
    }

    protected function getFactoryMethod(string $namespace): string
    {
        if (! $this->module) {
            return '';
        }

        $factoryClass = "Modules\\{$this->module}\\Database\\Factories\\{$this->model}Factory";
        
        return "\n    protected static function newFactory()\n    {\n        return \\{$factoryClass}::new();\n    }";
    }

    protected function initializeModule(): void
    {
        $basePath = base_path("Modules/{$this->module}");
        
        // 1. Create module.json if missing
        if (! File::exists("{$basePath}/module.json")) {
            $stub = File::get(__DIR__ . '/../../stubs/module.json.stub');
            $this->createFile("{$basePath}/module.json", $stub, [
                '{{Module}}'      => $this->module,
                '{{ModuleLower}}' => Str::lower($this->module),
            ]);
        }

        // 2. Create ServiceProvider if missing
        $providerPath = "{$basePath}/{$this->module}ServiceProvider.php";
        if (! File::exists($providerPath)) {
            $stub = File::get(__DIR__ . '/../../stubs/module-provider.stub');
            $this->createFile($providerPath, $stub, [
                '{{Module}}' => $this->module,
            ]);
            
            // Try to enable the module if nwidart is present
            try {
                $this->callSilent('module:enable', ['module' => $this->module]);
            } catch (\Exception $e) {
                // Silently skip if command not found
            }
        }
    }

    protected function generateMigration(): void
    {
        $table = Str::snake(Str::pluralStudly($this->model));
        $migrationExists = ! empty(glob(database_path("migrations/*create_{$table}_table.php")));

        if ($migrationExists) {
            $this->warn("  ⤳ Migration already exists for table [{$table}], skipping.");
            return;
        }

        $this->call('make:migration', [
            'name'     => "create_{$table}_table",
            '--create' => $table,
        ]);

        if ($this->option('soft-delete')) {
            $this->warn("  ⚠ Remember to add \$table->softDeletes(); to your migration.");
        }
    }

    // ──────────────────────────────────────────────────
    // Layer 2 — HTTP (Controller + Requests + Resource + Collection)
    // ──────────────────────────────────────────────────

    protected function generateStoreRequest(): void
    {
        $basePath  = $this->getBasePath();
        $subPath   = $this->getApiSubPath();
        $subNs     = $this->getApiSubNamespace();
        $namespace = $this->getBaseNamespace() . "\\Http\\Requests\\{$subNs}";
        $path      = "{$basePath}/Http/Requests/{$subPath}/Store{$this->model}Request.php";
        $table     = Str::snake(Str::pluralStudly($this->model));

        $rules = $this->resolveRules($table);

        $stub = File::get(__DIR__ . '/../../stubs/request.store.stub');
        $this->createFile($path, $stub, [
            '{{Namespace}}'     => $namespace,
            '{{ModelClass}}'    => $this->model,
            '{{ModelPath}}'     => $this->getBaseNamespace() . "\\Models\\{$this->model}",
            '{{modelVariable}}' => Str::camel($this->model),
            '{{Rules}}'         => $rules,
        ]);
    }

    protected function generateUpdateRequest(): void
    {
        $basePath  = $this->getBasePath();
        $subPath   = $this->getApiSubPath();
        $subNs     = $this->getApiSubNamespace();
        $namespace = $this->getBaseNamespace() . "\\Http\\Requests\\{$subNs}";
        $path      = "{$basePath}/Http/Requests/{$subPath}/Update{$this->model}Request.php";
        $table     = Str::snake(Str::pluralStudly($this->model));

        // Wrap rules with sometimes() for PATCH support
        $rules = $this->resolveRules($table, sometimes: true);

        $stub = File::get(__DIR__ . '/../../stubs/request.update.stub');
        $this->createFile($path, $stub, [
            '{{Namespace}}'     => $namespace,
            '{{ModelClass}}'    => $this->model,
            '{{ModelPath}}'     => $this->getBaseNamespace() . "\\Models\\{$this->model}",
            '{{modelVariable}}' => Str::camel($this->model),
            '{{Rules}}'         => $rules,
        ]);
    }

    protected function generateResource(): void
    {
        $basePath  = $this->getBasePath();
        $subPath   = $this->getApiSubPath();
        $subNs     = $this->getApiSubNamespace();
        $namespace = $this->getBaseNamespace() . "\\Http\\Resources\\{$subNs}";
        $path      = "{$basePath}/Http/Resources/{$subPath}/{$this->model}Resource.php";

        $stub = File::get(__DIR__ . '/../../stubs/resource.stub');
        $this->createFile($path, $stub, [
            '{{Namespace}}' => $namespace,
            '{{Class}}'     => "{$this->model}Resource",
        ]);
    }

    protected function generateCollection(): void
    {
        $basePath  = $this->getBasePath();
        $subPath   = $this->getApiSubPath();
        $subNs     = $this->getApiSubNamespace();
        $namespace = $this->getBaseNamespace() . "\\Http\\Resources\\{$subNs}";
        $path      = "{$basePath}/Http/Resources/{$subPath}/{$this->model}Collection.php";

        $stub = File::get(__DIR__ . '/../../stubs/collection.stub');
        $this->createFile($path, $stub, [
            '{{Namespace}}' => $namespace,
            '{{Class}}'     => $this->model,
        ]);
    }

    protected function generateController(): void
    {
        $basePath  = $this->getBasePath();
        $subPath   = $this->getApiSubPath();
        $subNs     = $this->getApiSubNamespace();
        $namespace = $this->getBaseNamespace() . "\\Http\\Controllers\\{$subNs}";
        $path      = "{$basePath}/Http/Controllers/{$subPath}/{$this->model}Controller.php";

        $serviceContractNs    = $this->getBaseNamespace() . '\\Services\\Contracts';
        $repositoryContractNs = $this->getBaseNamespace() . '\\Repositories\\Contracts';
        $resourceNs           = $this->getBaseNamespace() . "\\Http\\Resources\\{$subNs}";
        $storeRequestNs       = $this->getBaseNamespace() . "\\Http\\Requests\\{$subNs}";
        $dataNs               = $this->getBaseNamespace() . '\\DTOs';

        $stub = File::get(__DIR__ . '/../../stubs/controller.api.stub');

        $this->createFile($path, $stub, [
            '{{Namespace}}'          => $namespace,
            '{{ModelClass}}'         => $this->model,
            '{{ModelPath}}'          => $this->getBaseNamespace() . "\\Models\\{$this->model}",
            '{{ModelVariable}}'      => Str::camel($this->model),
            '{{ServiceContractPath}}' => $serviceContractNs,
            '{{StoreRequestImport}}' => "use {$storeRequestNs}\\Store{$this->model}Request;",
            '{{UpdateRequestImport}}' => "use {$storeRequestNs}\\Update{$this->model}Request;",
            '{{StoreRequestClass}}'  => "Store{$this->model}Request",
            '{{UpdateRequestClass}}' => "Update{$this->model}Request",
            '{{ResourceImport}}'     => "use {$resourceNs}\\{$this->model}Resource;",
            '{{CollectionImport}}'   => "use {$resourceNs}\\{$this->model}Collection;",
            '{{ResourceClass}}'      => "{$this->model}Resource",
            '{{CollectionClass}}'    => "{$this->model}Collection",
            '{{Class}}'              => "{$this->model}Controller",
            '{{DataImport}}'         => "use {$dataNs}\\{$this->model}Data;",
        ]);
    }

    // ──────────────────────────────────────────────────
    // Layer 3 — Service (Interface + Implementation)
    // ──────────────────────────────────────────────────

    protected function generateService(): void
    {
        $basePath       = $this->getBasePath();
        $baseNs         = $this->getBaseNamespace();
        $serviceNs      = "{$baseNs}\\Services";
        $contractNs     = "{$serviceNs}\\Contracts";
        $contractPath   = "{$basePath}/Services/Contracts";
        $servicePath    = "{$basePath}/Services";
        $modelNs        = "{$baseNs}\\Models\\{$this->model}";
        $dataNs         = "{$baseNs}\\DTOs\\{$this->model}Data";
        $repoContractNs = "{$baseNs}\\Repositories\\Contracts";
        $repoInterface  = "{$this->model}RepositoryInterface";

        // 1. Interface
        $interfaceStub = File::get(__DIR__ . '/../../stubs/service-interface.stub');
        $this->createFile("{$contractPath}/{$this->model}ServiceInterface.php", $interfaceStub, [
            '{{Namespace}}'  => $serviceNs,
            '{{Class}}'      => $this->model,
            '{{ModelClass}}' => $this->model,
            '{{ModelPath}}'  => $modelNs,
            '{{DataPath}}'   => $dataNs,
        ]);

        // 2. Implementation
        $serviceStub = File::get(__DIR__ . '/../../stubs/service.stub');
        $this->createFile("{$servicePath}/{$this->model}Service.php", $serviceStub, [
            '{{Namespace}}'          => $serviceNs,
            '{{Class}}'              => "{$this->model}Service",
            '{{ModelClass}}'         => $this->model,
            '{{ModelPath}}'          => $modelNs,
            '{{DataPath}}'           => $dataNs,
            '{{RepositoryInterface}}' => $repoInterface,
            '{{RepositoryContractPath}}' => $repoContractNs,
        ]);
    }

    // ──────────────────────────────────────────────────
    // Layer 4 — Repository (Interface + Implementation)
    // ──────────────────────────────────────────────────

    protected function generateRepository(): void
    {
        $basePath     = $this->getBasePath();
        $baseNs       = $this->getBaseNamespace();
        $repoNs       = "{$baseNs}\\Repositories";
        $contractNs   = "{$repoNs}\\Contracts";
        $contractPath = "{$basePath}/Repositories/Contracts";
        $repoPath     = "{$basePath}/Repositories";
        $modelNs      = "{$baseNs}\\Models\\{$this->model}";

        // 1. Interface
        $interfaceStub = File::get(__DIR__ . '/../../stubs/repository-interface.stub');
        $this->createFile("{$contractPath}/{$this->model}RepositoryInterface.php", $interfaceStub, [
            '{{Namespace}}' => $repoNs,
            '{{Class}}'     => $this->model,
        ]);

        // 2. Implementation
        $repoStub = File::get(__DIR__ . '/../../stubs/repository.stub');
        $this->createFile("{$repoPath}/{$this->model}Repository.php", $repoStub, [
            '{{Namespace}}' => $repoNs,
            '{{Class}}'     => "{$this->model}Repository",
            '{{ModelClass}}' => $this->model,
            '{{ModelPath}}' => $modelNs,
        ]);
    }

    // ──────────────────────────────────────────────────
    // Layer 5 — DTO + Policy + Test
    // ──────────────────────────────────────────────────

    protected function generateData(): void
    {
        $basePath  = $this->getBasePath();
        $namespace = $this->getBaseNamespace() . '\\DTOs';
        $path      = "{$basePath}/DTOs/{$this->model}Data.php";
        $table     = Str::snake(Str::pluralStudly($this->model));

        // Build typed readonly properties from DB schema
        $rawRules = [];
        if (in_array($table, array_column($this->analyzer->getTables(), 'name'))) {
            $rawRules = $this->analyzer->generateRules($table);
        }

        $properties = [];
        foreach ($rawRules as $col => $colRules) {
            $type       = $this->inferPhpType($colRules);
            $nullable   = in_array('nullable', $colRules) ? '?' : '';
            $ruleAttr   = "#[Rule(['" . implode("', '", $colRules) . "'])]";
            $properties[] = "{$ruleAttr}\n        public readonly {$nullable}{$type} \${$col}";
        }

        if (empty($properties)) {
            $properties[] = 'public readonly string $name';
        }

        $stub = File::get(__DIR__ . '/../../stubs/data.stub');
        $this->createFile($path, $stub, [
            '{{Namespace}}' => $namespace,
            '{{Class}}'     => "{$this->model}Data",
            '{{Properties}}' => implode(",\n        ", $properties),
        ]);
    }

    protected function generatePolicy(): void
    {
        $basePath  = $this->getBasePath();
        $namespace = $this->getBaseNamespace() . '\\Policies';
        $path      = "{$basePath}/Policies/{$this->model}Policy.php";

        $stub = File::get(__DIR__ . '/../../stubs/policy.stub');
        $this->createFile($path, $stub, [
            '{{Namespace}}'     => $namespace,
            '{{Class}}'         => "{$this->model}Policy",
            '{{ModelPath}}'     => $this->getBaseNamespace() . "\\Models\\{$this->model}",
            '{{ModelClass}}'    => $this->model,
            '{{ModelVariable}}' => Str::camel($this->model),
            '{{modelVariable}}' => Str::camel($this->model),
            '{{UserPath}}'      => 'App\\Models\\User',
            '{{UserClass}}'     => 'User',
        ]);
    }

    protected function generateTest(): void
    {
        $table = Str::snake(Str::pluralStudly($this->model));

        $path = $this->module
            ? base_path("Modules/{$this->module}/Tests/Feature/{$this->model}Test.php")
            : base_path("tests/Feature/{$this->model}Test.php");

        $stub = File::get(__DIR__ . '/../../stubs/test.stub');
        $this->createFile($path, $stub, [
            '{{ModelPath}}'      => $this->getBaseNamespace() . "\\Models\\{$this->model}",
            '{{ModelClass}}'     => $this->model,
            '{{ModelVariable}}'  => Str::camel($this->model),
            '{{ModelVariables}}' => Str::plural(Str::camel($this->model)),
            '{{Route}}'          => Str::kebab(Str::plural($this->model)),
            '{{Table}}'          => $table,
        ]);
    }

    protected function generateFactory(): void
    {
        $basePath  = $this->getBasePath();
        $namespace = $this->getBaseNamespace() . '\\Database\\Factories';
        $path      = "{$basePath}/Database/Factories/{$this->model}Factory.php";

        $stub = File::get(__DIR__ . '/../../stubs/factory.stub');
        $this->createFile($path, $stub, [
            '{{Namespace}}' => $namespace,
            '{{ModelPath}}' => $this->getBaseNamespace() . "\\Models\\{$this->model}",
        ]);
    }

    // ──────────────────────────────────────────────────
    // Optional generators
    // ──────────────────────────────────────────────────

    protected function generateNotification(): void
    {
        $name      = "{$this->model}Notification";
        $path      = app_path("Notifications/{$name}.php");
        $stub      = File::get(__DIR__ . '/../../stubs/notification.stub');

        $this->createFile($path, $stub, [
            '{{Namespace}}' => 'App\\Notifications',
            '{{Class}}'     => $name,
            '{{Model}}'     => $this->model,
        ]);
    }

    protected function generateEvent(): void
    {
        foreach (['Created', 'Updated', 'Deleted'] as $suffix) {
            $this->call('make:event', ['name' => "{$this->model}{$suffix}"]);
        }
    }

    // ──────────────────────────────────────────────────
    // Route registration
    // ──────────────────────────────────────────────────

    protected function registerRoute(): void
    {
        $routeFile = $this->module
            ? base_path("Modules/{$this->module}/Routes/api.php")
            : base_path('routes/api.php');

        // Create module route file if it doesn't exist
        if (! File::exists($routeFile)) {
            $dir = dirname($routeFile);
            if (! File::exists($dir)) {
                File::makeDirectory($dir, 0755, true);
            }
            File::put($routeFile, "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\n");
        }

        $subNs          = $this->getApiSubNamespace();
        $controllerNs   = $this->getBaseNamespace() . "\\Http\\Controllers\\{$subNs}";
        $controllerClass = "{$this->model}Controller";
        $routeName       = Str::kebab(Str::plural($this->model));

        $routeLine = "\nRoute::apiResource('{$routeName}', \\{$controllerNs}\\{$controllerClass}::class);";

        File::append($routeFile, $routeLine);
        $this->info("  ✓ Route registered in: {$routeFile}");
    }

    // ──────────────────────────────────────────────────
    // Smart Validation Logic
    // ──────────────────────────────────────────────────

    protected function resolveRules(string $table, bool $sometimes = false): string
    {
        $rawRules = [];
        if (in_array($table, array_column($this->analyzer->getTables(), 'name'))) {
            $rawRules = $this->analyzer->generateRules($table);
        }

        $rulesString = '';
        foreach ($rawRules as $col => $colRules) {
            if ($sometimes) {
                array_unshift($colRules, 'sometimes');
            }
            $rulesString .= "\n            '{$col}' => ['" . implode("', '", $colRules) . "'],";
        }

        return empty($rulesString) ? "\n            //" : $rulesString;
    }

    protected function inferPhpType(array $rules): string
    {
        if (in_array('integer', $rules)) return 'int';
        if (in_array('numeric', $rules)) return 'float';
        if (in_array('boolean', $rules)) return 'bool';
        if (in_array('date', $rules)) return 'string'; // Or Carbon if we want to be fancy
        return 'string';
    }

    // ──────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────

    protected function getTranslatableFields(): string
    {
        if (! $this->option('translatable')) {
            return '';
        }

        $fields = array_map('trim', explode(',', $this->option('translatable')));
        return "public \$translatable = ['" . implode("', '", $fields) . "'];";
    }

    protected function getEnterpriseTraits(): string
    {
        $traits = [];

        if ($this->option('with-logs')) {
            $traits[] = 'use \\Spatie\\Activitylog\\Traits\\LogsActivity;';
        }

        return implode("\n    ", $traits);
    }

    protected function createFile(string $path, string $stub, array $replacements): void
    {
        $directory = dirname($path);

        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $defaultReplacements = [
            '{{ModelClass}}' => $this->model,
            '{{Model}}'      => $this->model,
            '{{Module}}'     => $this->module ?? '',
        ];

        $replacements = array_merge($defaultReplacements, $replacements);
        $content = str_replace(array_keys($replacements), array_values($replacements), $stub);

        if (File::exists($path)) {
            if (! $this->confirm("File already exists: {$path}\nOverwrite?", false)) {
                $this->warn("  ⤳ Skipped: {$path}");
                return;
            }
        }

        File::put($path, $content);
        $this->line("  <fg=green>✓</> Created: <fg=cyan>{$path}</>");
    }
}
