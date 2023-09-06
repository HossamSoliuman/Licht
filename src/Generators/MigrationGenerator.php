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
        $filename = "{$timestamp}_create_{$migrationName}_table.php";

        $stub = file_get_contents(__DIR__ . '/../mystubs/migration.create.stub');
        $stub = str_replace(['{{ class }}', '{{ table }}'], [$migrationClassName, $migrationName], $stub);

        $fieldDefinitions = '';
        $lastField = array_key_last($fields);
        foreach ($fields as $fieldName => $fieldType) {
            $fieldMethod = $this->getFieldMethod($fieldType);
            if ($fieldMethod == 'foreignId') {
                $fieldDefinitions .= "\t\t\t\$table->{$fieldMethod}('{$fieldName}')->constrained()->cascadeOnDelete();;";
            } else {
                $fieldDefinitions .= "\t\t\t\$table->{$fieldMethod}('{$fieldName}');";
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

    private function getFieldMethod($fieldType)
    {
        $typeMap = [
            'foreignId' => 'foreignId',
            'image' => 'string',
            'file' => 'string',
            'date' => 'date',
            'datetime' => 'dateTime',
        ];

        return $typeMap[$fieldType] ?? $fieldType;
    }
}
