<?php

namespace Hossam\Licht\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

trait Helper
{
    public function wordCase($word, $case)
    {
        $cammel = Str::camel($word);
        $cases = [
            'model' => lcfirst($word),
            'Model' => ucfirst($word),
            'ModelName' =>  ucfirst($cammel),
            'modelName' =>  $cammel,
            'modelNames' => Str::plural($cammel),
            'ModelNames' => ucfirst(Str::plural($cammel)),
            'model-names' => Str::plural(Str::kebab($word)),
            'model-name' => Str::kebab($word),
        ];

        return $cases[$case] ?? null;
    }
}
