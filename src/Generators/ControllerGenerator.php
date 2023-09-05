<?php

namespace Hossam\Licht\Generators;

use Illuminate\Support\Str;

class ControllerGenerator
{
    public function create($model, $fields)
    {
        $modelVariable = Str::camel($model);
        $fileName = $model . 'Controller.php';

        // Determine if the model has file upload fields
        $hasFiles = $this->hasFiles($fields);

        if ($hasFiles) {
            $stub = $this->generateFileControllerStub($model, $hasFiles, $modelVariable);
        } else {
            $stub = file_get_contents(__DIR__ . '/../mystubs/controller.api.stub');
        }

        // Replace placeholders in the stub with actual values
        $stub = str_replace('{{ model }}', $model, $stub);
        $stub = str_replace('{{ modelVariable }}', $modelVariable, $stub);

        // Save the generated controller file
        $path = app_path("Http/Controllers/{$fileName}");
        file_put_contents($path, $stub);

        return $fileName;
    }

    private function hasFiles($fields)
    {
        $fieldsOfTypeFile = [];

        foreach ($fields as $name => $type) {
            if (Str::contains($type, ['file', 'file[]', 'image'])) {
                $fieldsOfTypeFile[$name] = $type;
            }
        }

        return $fieldsOfTypeFile;
    }

    public function generateFileControllerStub($model, $fields, $modelVariable)
    {
        $store = '';
        $update = '';
        $delete = '';

        foreach ($fields as $name => $type) {
            $TypeName = Str::ucfirst($type);
            $store .= "\n\t\t\$validData['{$name}'] = \$this->uploadFile(\$validData['{$name}'], {$model}::PathToStored{$TypeName}s);";
            $update .= "\n\t\tif (\$request->hasFile('{$name}')) {
                \$this->deleteFile(\${$modelVariable}->{$name});
                \$validData['{$name}'] = \$this->uploadFile(\$request->file('{$name}'), {$model}::PathToStored{$TypeName}s);
            }";
            $delete .= "\n\t\t\$this->deleteFile(\${$modelVariable}->{$name});";
        }

        $stub = file_get_contents(__DIR__ . '/../mystubs/controller.api.file.stub');
        $stub = str_replace('{{ store }}', $store, $stub);
        $stub = str_replace('{{ update }}', $update, $stub);
        $stub = str_replace('{{ delete }}', $delete, $stub);

        return $stub;
    }
}
