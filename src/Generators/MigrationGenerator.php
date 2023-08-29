<?php

namespace Hossam\Licht\Generators;

use Illuminate\Support\Str;

class MigrationGenerator
{
    public function create($model, $fields)
    {
        $migrationName = Str::plural(Str::snake($model));
        $migrationClassName = 'Create' . Str::studly($migrationName) . 'Table';

        $timestamp = now()->format('Y_m_d_His');
        $filename = "{$timestamp}_{$migrationName}_table.php";

        $stub = file_get_contents(__DIR__ . '/../mystubs/migration.create.stub');
        $stub = str_replace('{{ class }}', $migrationClassName, $stub);
        $stub = str_replace('{{ table }}', $migrationName, $stub);

        $fieldDefinitions = '';
        $lastField = end(array_keys($fields));
        foreach ($fields as $fieldName => $fieldType) {
            if ($fieldType === 'foreignId') {
                $fieldDefinitions .= "\t\t\t\$table->{$fieldType}('{$fieldName}')->constrained()->cascadeOnDelete();";
            } else {
                $fieldDefinitions .= "\t\t\t\$table->{$fieldType}('{$fieldName}');";
            }
            if ($fieldName !== $lastField) {
                $fieldDefinitions .= "\n";
            }
        }
        $stub = str_replace('{{ fields }}', $fieldDefinitions, $stub);

        $path = database_path("migrations/{$filename}");
        file_put_contents($path, $stub);

        return $filename;
    }
}
