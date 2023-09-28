<?php

namespace Hossam\Licht\Generators;

use Illuminate\Support\Str;

class ModelGenerator
{
    public function create($model, $fields)
    {
        $stub = file_get_contents(__DIR__ . '/../mystubs/model.stub');
        $fileName = $model . '.php';

        $stub = str_replace('{{ class }}', $model, $stub);

        $fillables = '';
        $relations = '';
        $jsonType = '';
        $lastField = array_key_last($fields);
        foreach ($fields as $fieldName => $fieldType) {
            $fillables .= "\t\t\t'{$fieldName}',";
            if ($fieldName !== $lastField) {
                $fillables .= "\n";
            }
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

                    $relations .= "\n\tpublic function {$parentMethod}(){\n\t\treturn \$this->belongsTo({$parent}::class);\n\t}";
                }
            }
            if ($fieldType == 'json') {
                $useAttribute = 'use Illuminate\Database\Eloquent\Casts\Attribute;';
                $stub = str_replace('{{ use Attribute }}', $useAttribute, $stub);

                $method_name = Str::camel($fieldName);
                $jsonMethod = "\n\tprotected function $method_name(): Attribute\n\t{\n\t\treturn Attribute::make(\n\t\t\tget: fn (\$value) => json_decode(\$value, true),\n\t\t\tset: fn (\$value) => json_encode(\$value),\n\t\t);\n\t}";

                $jsonType .= $jsonMethod;
            }
        }

        //model/type/name

        $storedFilesPath = $this->generateFilesPath($model, $fields);

        $stub = str_replace('{{ stored files path }}', $storedFilesPath, $stub);
        $stub = str_replace('{{ fields }}', $fillables, $stub);
        $stub = str_replace('{{ relations }}', $relations, $stub);
        $stub = str_replace('{{ jsonMethod }}', $jsonType, $stub);

        $path = app_path("Models/{$fileName}");
        file_put_contents($path, $stub);
        return $fileName;
    }
    public function generateFilesPath($model, $fields)
    {
        $modelFolder = Str::camel($model);
        $storedFilesPath = null;
        foreach ($fields as $name => $type) {
            $name = Str::plural($name);
            $TypeName = Str::ucfirst($type);
            if (Str::contains($type, ['file', 'image'])) {
                $storedFilesPath .= "const PathToStored{$TypeName}s='{$modelFolder}/{$type}s/{$name}';";
            }
        }
        return $storedFilesPath;
    }
}
