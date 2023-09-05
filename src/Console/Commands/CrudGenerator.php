<?php

namespace Hossam\Licht\Console\Commands;

use Hossam\Licht\Generators\ResourceGenerator;
use Hossam\Licht\Generators\ControllerGenerator;
use Hossam\Licht\Generators\MigrationGenerator;
use Hossam\Licht\Generators\ModelGenerator;
use Hossam\Licht\Generators\RequestsGenerator;
use Illuminate\Console\Command;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Illuminate\Support\Str;

class CrudGenerator extends Command
{
    protected $signature = 'licht:crud {model}';
    protected $description = 'Generate CRUD operations for a model';
    protected $migrationName;

    public function handle()
    {
        $modelName = $this->getModelName();

        $fields = $this->gatherFields();

        $this->displayModelFields($fields);

        $this->generateCrudComponents($modelName, $fields);

        $this->displayGeneratedFiles($modelName);
    }

    protected function getModelName()
    {
        $modelName = ucfirst($this->argument('model'));

        if (!preg_match('/^[A-Z][a-zA-Z]*$/', $modelName)) {
            $this->error('Invalid model name. Model names should start with a capital letter and contain only letters.');
            exit(1);
        }

        return $modelName;
    }

    protected function gatherFields()
    {
        $fields = [];
        $askForFields = true;

        while ($askForFields) {
            $fieldType = $this->askFieldType();
            $fieldName = $this->ask('Enter field name', 'name');

            if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $fieldName)) {
                $this->error('Invalid field name. Field names should start with a letter or underscore and contain only letters, numbers, and underscores.');
                continue;
            }

            $fields[$fieldName] = $fieldType;

            $this->displayModelFields($fields);

            if (!$this->confirm('Add more fields?', true)) {
                $askForFields = false;
            }
        }

        return $fields;
    }

    protected function displayModelFields($fields)
    {
        $this->info("\nModel Fields:");

        $this->table(
            ['Field Name', 'Field Type'],
            collect($fields)->map(function ($type, $field) {
                return [$field, $type];
            })->toArray()
        );
    }

    protected function askFieldType()
    {
        $question = new ChoiceQuestion(
            'Choose field type',
            [
                'string',
                'integer',
                'text',
                'foreignId',
                'image',
                'file',
                'date',
                'datetime'
            ],
            0
        );
        return $this->choice($question->getQuestion(), $question->getChoices(), 0);
    }

    protected function generateCrudComponents($modelName, $fields)
    {
        $this->info("Generating CRUD components for {$modelName}...");

        $generators = [
            'Model' => new ModelGenerator,
            'Requests' => new RequestsGenerator,
            'Resource' => new ResourceGenerator,
            'Controller' => new ControllerGenerator,
            'Migration' => new MigrationGenerator,
        ];

        $totalSteps = count($generators);
        $bar = $this->output->createProgressBar($totalSteps);

        // Customize the progress bar style
        $bar->setBarWidth(50);
        $bar->setBarCharacter('<comment>=</comment>');
        $bar->setEmptyBarCharacter('-');
        $bar->setProgressCharacter('<info>></info>');
        $bar->start();
        foreach ($generators as $component => $generator) {
            $bar->setMessage("Generating $component...");
            if ($component == 'Migration') {
                $this->migrationName = $generator->create($modelName, $fields);
            }
            $bar->advance();
            usleep(200000);
        }
        $bar->setMessage("All components generated!");
        $bar->finish();
        sleep(1);
    }


    protected function displayGeneratedFiles($modelName)
    {
        $this->info("\nGenerated files for {$modelName}:");
        $generators = [
            'Model' => app_path("Models/{$modelName}.php"),
            'Store Request' => app_path("Http/Requests/Store{$modelName}Request.php"),
            'Update Request' => app_path("Http/Requests/Update{$modelName}Request.php"),
            'Resource' => app_path("Http/Resources/{$modelName}Resource.php"),
            'Controller' => app_path("Http/Controllers/{$modelName}Controller.php"),
            'Migration' => database_path("migrations/{$this->migrationName}"),
        ];
        $this->table(
            ['Component', 'Path'],
            collect($generators)->map(function ($path, $component) {
                return [$component, $path];
            })->toArray(),
            'box'
        );
    }

    protected function getMigrationFileName($modelName)
    {
        $timestamp = now()->format('Y_m_d_His');
        return "{$timestamp}_create_" . Str::snake(Str::plural($modelName)) . "_table.php";
    }
}
