<?php

namespace Muhammad\StarterKit\Commands;

use Illuminate\Console\Command;
use Muhammad\StarterKit\Services\DBAnalyzer;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SmartSyncRelationsCommand extends Command
{
    protected $signature = 'smart:sync-relations';
    protected $description = 'Scan DB and sync relationships into Models';

    protected $analyzer;

    public function __construct(DBAnalyzer $analyzer)
    {
        parent::__construct();
        $this->analyzer = $analyzer;
    }

    public function handle()
    {
        $this->info("Scanning models and database schema...");

        $models = $this->getModels();

        foreach ($models as $modelName) {
            $this->syncModel($modelName);
        }

        $this->info("Relationships synced successfully!");
    }

    protected function getModels(): array
    {
        $path = app_path('Models');
        if (!File::exists($path)) return [];

        return collect(File::files($path))
            ->map(fn($file) => $file->getBasename('.php'))
            ->filter(fn($name) => $name !== 'User') // Skip user for now or handle carefully
            ->toArray();
    }

    protected function syncModel(string $modelName)
    {
        $fullClass = "App\\Models\\{$modelName}";
        $modelInstance = new $fullClass;
        $table = $modelInstance->getTable();

        $relationships = $this->analyzer->identifyRelationships($table);
        $this->info("Analyzing {$modelName} (Table: {$table})...");

        if (empty($relationships)) {
            $this->warn("No foreign keys found for {$table}.");
            return;
        }

        $modelPath = app_path("Models/{$modelName}.php");
        $content = File::get($modelPath);

        foreach ($relationships['belongsTo'] ?? [] as $rel) {
            $methodName = $rel['method'];
            if (Str::contains($content, "function {$methodName}()")) {
                $this->line(" - Relationship {$methodName}() already exists in {$modelName}. Skipping.");
                continue;
            }

            $methodCode = "\n    public function {$methodName}()\n    {\n        return \$this->belongsTo(\\{$rel['model']}::class, '{$rel['foreign_key']}', '{$rel['owner_key']}');\n    }\n";
            
            // Insert before the last closing brace
            $content = preg_replace('/}([^}]*)$/', $methodCode . '}$1', $content);
            $this->info(" + Added belongsTo: {$methodName}() to {$modelName}.");
        }

        File::put($modelPath, $content);
    }
}
