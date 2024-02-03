<?php

namespace Hossam\Licht\Generators;

use Illuminate\Support\Str;
use Hossam\Licht\Traits\Helper;

class ViewGenerator
{
    use Helper;
    public function create($model, $items)
    {
        $modelName = Str::singular($model);
        $pluralModelName = Str::plural($modelName);
        $fileName = strtolower($pluralModelName) . '.blade.php';
        $stub = $this->generateViewStub($modelName, $pluralModelName, $items);
        $path = resource_path("views/$fileName");
        file_put_contents($path, $stub);
        return $fileName;
    }

    private function generateViewStub($modelName, $pluralModelName, $items)
    {
        $modelSmall = lcfirst($modelName);
        $Models = Str::plural($modelName);
        $models = Str::plural($modelSmall);
        $stub = file_get_contents(__DIR__ . '/../mystubs/view.stub');
        $stub = str_replace('{{ $modelName }}', $modelName, $stub);
        $stub = str_replace('{{ $pluralModelName }}', $pluralModelName, $stub);
        $stub = str_replace('{{ $model }}', $modelSmall, $stub);
        $stub = str_replace('{{ $Models }}', $Models, $stub);
        $stub = str_replace('{{ $models }}', $models, $stub);
        $stub = str_replace('{{ $formFields }}', $this->formFields($items, $modelName), $stub);
        $stub = str_replace('{{ $tableHeader }}', $this->tableHeadr($items, $modelName), $stub);
        $stub = str_replace('{{ $tableBody }}', $this->tableBody($items, $modelName), $stub);
        $stub = str_replace('{{ $jsFields }}', $this->jsFields($items, $modelName), $stub);
        return $stub;
    }


    public function formFields($fields, $model)
    {
        $lines = '';
        foreach ($fields as $fieldName => $fieldType) {
            $lines .= "\n\t\t\t\t\t\t\t\t\t" . '<div class="form-group">' . "\n";
            $lines .= "\t\t\t\t\t\t\t\t\t\t" . '<input type="text" name="' . $fieldName . '" class="form-control" placeholder="' . $model . ' ' . $fieldName . '" required>' . "\n";
            $lines .= "\t\t\t\t\t\t\t\t\t" . '</div>';
        }
        return $lines;
    }
    public function tableHeadr($fields, $model)
    {
        $lines = '';
        foreach ($fields as $fieldName => $fieldType) {
            $fieldName = $this->wordCase($fieldName, 'Model');
            $lines .= "\n\t\t\t\t\t\t\t" . '<th> ' . $fieldName . '</th>';
        }
        return $lines;
    }
    public function tableBody($fields, $model)
    {
        $lines = '';
        $model = $this->wordCase($model, 'model');
        foreach ($fields as $fieldName => $fieldType) {
            $lines .= "\n\t\t\t\t\t\t\t" . '<td class=" ' . $model . '-' . $fieldName . '">{{ $' . $model . '->' . $fieldName . ' }}</td>';
        }
        return $lines;
    }
    public function jsFields($fields, $model)
    {

        // var PostName = $(this).closest("tr").find(".post-name").text();
        // $('#editModal input[name="name"]').val(PostName);
        $model = $this->wordCase($model, 'Model');
        $modelSmall = $this->wordCase($model, 'model');
        $lines = '';
        foreach ($fields as $fieldName => $fieldType) {
            $fieldName = $this->wordCase($fieldName, 'Model');
            $fieldNameSmall = $this->wordCase($fieldName, 'model');

            $lines .= "\n\t\t\t\t" . 'var ' . $model . $fieldName . ' = $(this).closest("tr").find(".' . $modelSmall . '-' . $fieldNameSmall . '").text();';
            $lines .= "\n\t\t\t\t" . "$('#editModal input[name=" . '"' . $fieldNameSmall . '"' . "]').val(" . $model . $fieldName . ');';
        }
        return $lines;
    }
}
