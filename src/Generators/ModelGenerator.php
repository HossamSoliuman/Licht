<?php

namespace Hossam\Licht\Generators;

use Illuminate\Support\Str;
use Hossam\Licht\Traits\Helper;

class ModelGenerator
{
    use Helper;
    public function create($model, $fields)
    {
        $stub = $this->getStub();
        $fileName = $this->generateFileName($model);

        $stub = $this->replaceClassName($stub, $model);
        [$fillables, $relations, $jsonType, $filesPaths] = $this->processFields($fields, $model, $stub);

        $stub = $this->replacePlaceholders($stub, $fillables, $relations, $jsonType, $filesPaths);

        $this->storeModelFile($fileName, $stub);

        return $fileName;
    }

    protected function getStub()
    {
        return file_get_contents(__DIR__ . '/../mystubs/model.stub');
    }

    protected function generateFileName($model)
    {
        return $model . '.php';
    }

    protected function replaceClassName($stub, $model)
    {
        return str_replace('{{ class }}', $model, $stub);
    }

    protected function processFields($fields, $model, $stub)
    {
        $fillables = '';
        $relations = '';
        $jsonType = '';
        $filesPaths = '';
        foreach ($fields as $fieldName => $fieldType) {
            $fillables .= $this->generateFillable($fieldName);
            $relations .= $this->generateRelation($fieldName, $fieldType, $model);
            $jsonType .= $this->generateJsonType($fieldName, $fieldType, $stub);
            $filesPaths .= $this->generateFilesPath($model, $fieldName, $fieldType);
        }

        return [$fillables, $relations, $jsonType, $filesPaths];
    }

    protected function generateFillable($fieldName)
    {
        return "\t\t\t'$fieldName',\n";
    }

    protected function generateRelation($fieldName, $fieldType, $model)
    {
        $relation = '';

        if ($fieldType == 'foreignId') {
            $parent = Str::studly(Str::remove('_id', $fieldName));

            if (class_exists("App\\Models\\{$parent}")) {
                $parentMethod = Str::camel($parent);
                $childMethod = Str::plural(Str::camel($model));
                $childsPath = app_path("Models/{$parent}.php");

                $lines = file($childsPath);
                $lastLine = array_pop($lines);

                $childRelation = "\n\tpublic function {$childMethod}(){\n\t\treturn \$this->hasMany({$model}::class);\n\t}\n}";
                $newLastLine = $childRelation;

                array_push($lines, $newLastLine);
                $newContent = implode('', $lines);
                file_put_contents($childsPath, $newContent);

                $relation .= "\n\tpublic function {$parentMethod}(){\n\t\treturn \$this->belongsTo({$parent}::class);\n\t}";
            }
        }

        return $relation;
    }

    protected function generateJsonType($fieldName, $fieldType, $stub)
    {
        $jsonMethod = '';

        if ($fieldType == 'json') {
            $method_name = Str::camel($fieldName);
            $jsonMethod = "\n\tprotected function $method_name(): Attribute\n\t{\n\t\treturn Attribute::make(\n\t\t\tget: fn (\$value) => json_decode(\$value, true),\n\t\t\tset: fn (\$value) => json_encode(\$value),\n\t\t);\n\t}";
        }

        return $jsonMethod;
    }
    protected function generateFilesPath($model, $fieldName, $fieldType)
    {
        $modelFolder = $this->wordCase($model, 'model-names');
        $name = $this->wordCase($fieldName, 'models');
        $TypeName = $this->wordCase($fieldType, 'Model');
        if (Str::contains($fieldType, ['file', 'image'])) {
            return "const PathToStored{$TypeName}s='{$modelFolder}/{$fieldType}s/{$name}';\n";
        }
    }


    protected function replacePlaceholders($stub, $fillables, $relations, $jsonType, $filesPaths)
    {
        $stub = str_replace('{{ fillables }}', $fillables, $stub);
        $stub = str_replace('{{ relations }}', $relations, $stub);
        $stub = str_replace('{{ jsonMethod }}', $jsonType, $stub);
        $stub = str_replace('{{ stored files path }}', $filesPaths, $stub);
        if (Str::length($jsonType != '')) {
            $useAttribute = 'use Illuminate\Database\Eloquent\Casts\Attribute;';
            $stub = str_replace('{{ use Attribute }}', $useAttribute, $stub);
        }
        $stub = str_replace('{{ use Attribute }}', '', $stub);

        return $stub;
    }

    protected function storeModelFile($fileName, $stub)
    {
        $path = app_path("Models/{$fileName}");
        file_put_contents($path, $stub);
    }
}
