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

    public function formFields($fields)
    {
        $lines = '';
        foreach ($fields as $fieldName => $fieldType) {
            switch ($fieldType) {
                case 'foreignId':
                    $lines .= $this->generateForeignIdField($fieldName);
                    break;
                case 'image':
                    $lines .= $this->generateFileField($fieldName, 'image/*');
                    break;
                case 'text':
                    $lines .= $this->generateTextField($fieldName);
                    break;
                case 'file':
                    $lines .= $this->generateFileField($fieldName, 'application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                    break;
                default:
                    $fieldType = $this->getFieldType($fieldType);
                    $placeholder = $field['placeholder'] ?? $fieldName;
                    $lines .= $this->generateInputField($fieldName, $fieldType, $placeholder);
                    break;
            }
        }
        return $lines;
    }
    protected function generateTextField($fieldName)
    {
        $lines = "\n\t\t\t\t\t\t\t\t\t" . '<div class="form-group">' . "\n";
        $lines .= "\t\t\t\t\t\t\t\t\t\t" . '<textarea name="' . $fieldName . '" class="form-control" placeholder="' . $fieldName . '" required rows="3"></textarea>' . "\n";
        $lines .= "\t\t\t\t\t\t\t\t\t\t\t\t" . '</div>' . "\n";
        return $lines;
    }

    protected function generateForeignIdField($fieldName)
    {
        $parent = Str::remove('_id', $fieldName);
        $parentSingal = $this->wordCase($parent, 'modelName');
        $parent = $this->wordCase(Str::remove('_id', $fieldName), 'modelNames');
        $lines = "\n\t\t\t\t\t\t\t\t\t" . '<div class="form-group">' . "\n";
        $lines .= "\t\t\t\t\t\t\t\t\t\t" . '<select class="form-control" name="' . $fieldName . '" id="' . $fieldName . '">' . "\n";
        $lines .= "\t\t\t\t\t\t\t\t\t\t\t" . '@foreach ($' . $parent . ' as $' . $parentSingal . ")\n";
        $lines .= "\t\t\t\t\t\t\t\t\t\t\t\t" . '<option value="{{ $' . $parentSingal . '->id }}">{{ $' . $parentSingal . '->name }}</option>' . "\n";
        $lines .= "\t\t\t\t\t\t\t\t\t\t\t" . '@endforeach' . "\n";
        $lines .= "\t\t\t\t\t\t\t\t\t\t" . '</select>' . "\n";
        $lines .= "\t\t\t\t\t\t\t\t\t" . '</div>' . "\n";
        return $lines;
    }

    protected function generateFileField($fieldName, $accept)
    {
        $lines = "\n\t\t\t\t\t\t\t\t\t" . '<div class="form-group">' . "\n";
        $lines .= "\t\t\t\t\t\t\t\t\t\t" . '<input type="file" accept="' . $accept . '" name="' . $fieldName . '" id="' . $fieldName . '" class="form-control" required>' . "\n";
        $lines .= "\t\t\t\t\t\t\t\t\t" . '</div>' . "\n";
        return $lines;
    }

    protected function generateInputField($fieldName, $fieldType, $placeholder)
    {
        $lines = "\n\t\t\t\t\t\t\t\t\t" . '<div class="form-group">' . "\n";
        $lines .= "\t\t\t\t\t\t\t\t\t\t" . '<input type="' . $fieldType . '" name="' . $fieldName . '" id="' . $fieldName . '" class="form-control" placeholder="' . $placeholder . '" required>' . "\n";
        $lines .= "\t\t\t\t\t\t\t\t\t\t\t\t" . '</div>' . "\n";
        return $lines;
    }

    protected function getFieldType($fieldType)
    {
        $types = [
            'string' => 'text',
            'integer' => 'number',
            'date' => 'date',
            'datetime' => 'datetime',
        ];
        return $types[$fieldType] ?? 'text';
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
