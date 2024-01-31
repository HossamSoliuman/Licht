<?php

namespace Hossam\Licht\Generators;

use Illuminate\Support\Str;

class ViewGenerator
{
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
        $stub = str_replace('{{ $items }}', $this->generateItemsList($items, $modelName), $stub);
        return $stub;
    }

    private function generateItemsList($items, $modelName)
    {
        $list = '';
        foreach ($items as $item) {
            $list .= "\t\t\t\t\t\t<tr data-{{ $modelName }}-id=\"{{ \${$modelName}->id }}\">\n";
            $list .= "\t\t\t\t\t\t\t<td class=\"{{ $modelName }}-name\">{{ \${$modelName}->name }}</td>\n";
            $list .= "\t\t\t\t\t\t\t<td class=\"d-flex\">\n";
            $list .= "\t\t\t\t\t\t\t\t<button type=\"button\" class=\"btn btn-warning btn-edit\" data-toggle=\"modal\" data-target=\"#editModal\">\n";
            $list .= "\t\t\t\t\t\t\t\t\tEdit\n";
            $list .= "\t\t\t\t\t\t\t\t</button>\n";
            $list .= "\t\t\t\t\t\t\t\t<form action=\"{{ route('{$modelName}.destroy', ['{$modelName}' => \${$modelName}->id]) }}\" method=\"post\">\n";
            $list .= "\t\t\t\t\t\t\t\t\t@csrf\n";
            $list .= "\t\t\t\t\t\t\t\t\t@method('DELETE')\n";
            $list .= "\t\t\t\t\t\t\t\t\t<button type=\"submit\" class=\"ml-3 btn btn-danger\">Delete</button>\n";
            $list .= "\t\t\t\t\t\t\t\t</form>\n";
            $list .= "\t\t\t\t\t\t\t</td>\n";
            $list .= "\t\t\t\t\t\t</tr>\n";
        }
        return $list;
    }
}
