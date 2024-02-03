<?php
namespace Hossam\Licht\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

trait Helper
{
    public function wordCase($word, $case)
    {
        $cammel = Str::camel($word);
        // model Models ModelNames modelNames model-names
        $cases = [
            'model' => lcfirst($word),
            'Model' => ucfirst($word),
            'modelName' => $cammel,
            'models' => Str::plural($cammel),
            'Models' => ucfirst(Str::plural($word)),
            'model-names' => Str::plural(Str::kebab($word)),
        ];

        return $cases[$case] ?? null;
    }
}
