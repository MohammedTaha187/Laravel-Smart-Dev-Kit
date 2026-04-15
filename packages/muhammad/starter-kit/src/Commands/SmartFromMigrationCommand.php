<?php

namespace Muhammad\StarterKit\Commands;

use Illuminate\Console\Command;
use Muhammad\StarterKit\Services\DBAnalyzer;
use Illuminate\Support\Str;

class SmartFromMigrationCommand extends Command
{
    protected $signature = 'smart:from-migration {table}';
    protected $description = 'Generate full feature from an existing table';

    protected $analyzer;

    public function __construct(DBAnalyzer $analyzer)
    {
        parent::__construct();
        $this->analyzer = $analyzer;
    }

    public function handle()
    {
        $table = $this->argument('table');
        $this->info("Analyzing table: {$table}...");

        if (!in_array($table, array_column($this->analyzer->getTables(), 'name'))) {
            $this->error("Table '{$table}' not found in database.");
            return;
        }

        $modelName = Str::studly(Str::singular($table));
        $columns = $this->analyzer->getColumns($table);
        $hasSoftDeletes = collect($columns)->contains('name', 'deleted_at');

        $this->info("Building feature for {$modelName}...");

        $this->call('smart:crud', [
            'name' => $modelName,
            '--api' => true,
            '--with-service' => true,
            '--with-repository' => true,
            '--with-resource' => true,
            '--with-request' => true,
            '--with-policy' => true,
            '--with-tests' => true,
            '--soft-delete' => $hasSoftDeletes,
        ]);

        $this->info("Feature from migration '{$table}' completed!");
    }
}
