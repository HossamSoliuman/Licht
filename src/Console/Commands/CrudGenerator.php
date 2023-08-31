<?php

namespace Hossam\Licht\Console\Commands;

use App\Licht\Services\ResourceGenerator;
use Hossam\Licht\Generators\ControllerGenerator;
use Hossam\Licht\Generators\MigrationGenerator;
use Hossam\Licht\Generators\ModelGenerator;
use Hossam\Licht\Generators\RequestsGenerator;
use Illuminate\Console\Command;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class CrudGenerator extends Command
{
    protected $signature = 'licht:model {name}';
    protected $description = 'Generate CRUD operations for a model';

    public function handle()
    {
        $this->info('Welcome to the CRUD Generator');

        $modelName = $this->argument('name');
        $fields = $this->gatherFields();

        $modelGenerator = new ModelGenerator;
        $modelGenerator->create($modelName, $fields);

        $requests = new RequestsGenerator;
        $requests->create($modelName, $fields);

        $resource = new ResourceGenerator;
        $resource->create($modelName, $fields);

        $controller = new ControllerGenerator;
        $controller->create($modelName, $fields);

        $migrationGenerator = new MigrationGenerator;
        $migrationFilename = $migrationGenerator->create($modelName, $fields);

        $this->line("CRUD operations generated for {$modelName}.");
        $this->line("Migration created: {$migrationFilename}");
    }

    protected function gatherFields()
    {
        $fields = [];
        $askForFields = true;

        while ($askForFields) {
            $fieldType = $this->askFieldType();
            $fieldName = $this->ask('Enter field name', 'name');

            $fields[$fieldName] = $fieldType;

            if (!$this->confirm('Add more fields?', true)) {
                $askForFields = false;
            }
        }

        return $fields;
    }

    protected function askFieldType()
    {
        $question = new ChoiceQuestion(
            'Choose field type',
            ['string', 'integer', 'text', 'foreignId'],
            0
        );

        return $this->choice($question->getQuestion(), $question->getChoices(), 0);
    }
}
