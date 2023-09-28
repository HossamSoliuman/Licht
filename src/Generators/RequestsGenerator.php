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
            $fieldRules = [];

            if ($fieldType === 'foreignId') {
                $table = $this->getForeignKeyTable($fieldName);
                $fieldRules[] = 'integer';
                $fieldRules[] = "exists:{$table},id";
            } elseif ($fieldType === 'text') {
                $fieldRules[] = 'string';
            } elseif ($fieldType === 'image') {
                $fieldRules = ['file', 'image', 'mimes:jpeg,png,jpg,gif'];
            } elseif ($fieldType === 'file') {
                $fieldRules = ['file', 'mimes:pdf,doc,docx,xls,xlsx'];
            } elseif ($fieldType === 'string') {
                $fieldRules = ['string', 'max:255'];
            } elseif ($fieldType === 'date') {
                $fieldRules[] = 'date';
            } elseif ($fieldType === 'datetime') {
                $fieldRules[] = 'date_format:Y-m-d H:i:s';
            } elseif ($fieldType === 'json') {
                $fieldRules[] = 'array';
            } else {
                $fieldRules[] = "{$fieldType}";
            }

            $fieldRules[] = "{$requiredOrNullable}";
            $rules .= "\t\t\t'{$fieldName}' => [" . implode(', ', array_map(function ($rule) {
                return "'$rule'";
            }, $fieldRules)) . "],";

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
