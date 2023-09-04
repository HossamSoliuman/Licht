<?php

namespace Hossam\Licht\Generators;

use Illuminate\Support\Str;

class RequestsGenerator
{
    public function create($model, $fields)
    {
        $storeClass = 'Store' . $model . 'Request';
        $updateClass = 'Update' . $model . 'Request';
        $storeFileName = $storeClass . '.php';
        $updateFileName = $updateClass . '.php';

        $stub = file_get_contents(__DIR__ . '/../mystubs/request.stub');
        $storeStub = str_replace('{{ class }}', $storeClass, $stub);
        $updateStub = str_replace('{{ class }}', $updateClass, $stub);

        $storeRules = $this->generateRules($fields, 'required');
        $updateRules = $this->generateRules($fields, 'nullable');

        $storeStub = str_replace('{{ rules }}', $storeRules, $storeStub);
        $updateStub = str_replace('{{ rules }}', $updateRules, $updateStub);

        $storePath = app_path("Http/Requests/{$storeFileName}");
        $updatePath = app_path("Http/Requests/{$updateFileName}");

        file_put_contents($storePath, $storeStub);
        file_put_contents($updatePath, $updateStub);
        return ['storeFile' => $storeClass, 'updateFile' => $updateClass];
    }

    private function generateRules($fields, $requiredOrNullable)
    {
        $rules = '';
        $lastField = array_key_last($fields);
        foreach ($fields as $fieldName => $fieldType) {
            if ($fieldType === 'foreignId') {
                $table = $this->getForeignKeyTable($fieldName);
                $rules .= "\t\t\t'{$fieldName}' => '{$requiredOrNullable}|integer|exists:{$table},id',";
            } elseif ($fieldType === 'text') {
                $rules .= "\t\t\t'{$fieldName}' => '{$requiredOrNullable}|string',";
            } else {
                $rules .= "\t\t\t'{$fieldName}' => '{$requiredOrNullable}|{$fieldType}',";
            }
            if ($fieldName !== $lastField) {
                $rules .= "\n";
            }
        }

        return $rules;
    }

    public function getForeignKeyTable($foreignKey)
    {
        return Str::plural(Str::remove('_id', $foreignKey));
    }
}
