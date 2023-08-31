<?php

namespace App\Licht\Services;

use Illuminate\Support\Str;

class ResourceGenerator
{
    public function create($model, $fields)
    {
        $stub = file_get_contents(__DIR__ . '/../mystubs/resource.stub');
        $fileName = $model . 'Resource.php';
        $stub = str_replace('{{ model }}', $model, $stub);
        $columns = '';
        $lastField = array_key_last($fields);

        foreach ($fields as $fieldName => $fieldType) {
            if ($fieldType === 'foreignId') {
                $relatedResource = $this->getRelatedResourceName($fieldName);
                $columns .= "\t\t\t'{$this->getRelatedModelName($fieldName)}' => {$relatedResource}::make(\$this->whenLoaded('{$this->getRelatedModelName($fieldName)}')),";
                // 'user' => UserResource::make($this->whenLoaded('user')),
            } else {
                $columns .= "\t\t\t'{$fieldName}' => $" . "this->{$fieldName},";
            }

            if ($fieldName !== $lastField) {
                $columns .= "\n";
            }
        }

        $stub = str_replace('{{ fields }}', $columns, $stub);
        $path = app_path("Http/Resources/{$fileName}");
        file_put_contents($path, $stub);
    }

    private function getRelatedResourceName($foreignKey)
    {
        $relatedModel = Str::studly(Str::remove('_id', $foreignKey));
        return "{$relatedModel}Resource";
    }
    private function getRelatedModelName($foreignKey)
    {
        $parent = Str::studly(Str::remove('_id', $foreignKey));
        $parentMethod = Str::camel($parent);
        return $parentMethod;
    }
}
