<?php

namespace Hossam\Licht\Console\Commands;

use Hossam\Licht\Generators\ResourceGenerator;
use Hossam\Licht\Generators\ControllerGenerator;
use Hossam\Licht\Generators\MigrationGenerator;
use Hossam\Licht\Generators\ModelGenerator;
use Hossam\Licht\Generators\RequestsGenerator;
use Illuminate\Console\Command;
use Symfony\Component\Console\Question\ChoiceQuestion;

class CrudGenerator extends Command
{
    protected $signature = 'licht:model {name}';
    protected $description = 'Generate CRUD operations for a model';

    public function handle()
    {
        $this->info('Welcome to the CRUD Generator');

        // Get the model name and fields from user input
        $modelName = $this->getModelName();
        $fields = $this->gatherFields();

        // Generate CRUD components
        $this->generateCrudComponents($modelName, $fields);

        $this->line("CRUD operations generated for {$modelName}.");
    }

    protected function getModelName()
    {
        // Ask the user for the model name and validate it
        $modelName = $this->ask('Enter the model name (e.g., Post)', 'Post');

        if (!preg_match('/^[A-Z][a-zA-Z]*$/', $modelName)) {
            $this->error('Invalid model name. Model names should start with a capital letter and contain only letters.');
            return $this->getModelName(); // Recursively ask for a valid name
        }

        return $modelName;
    }

    protected function gatherFields()
    {
        $fields = [];
        $askForFields = true;

        while ($askForFields) {
            // Ask for field type and name
            $fieldType = $this->askFieldType();
            $fieldName = $this->ask('Enter field name', 'name');

            // Validate the field name
            if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $fieldName)) {
                $this->error('Invalid field name. Field names should start with a letter or underscore and contain only letters, numbers, and underscores.');
                continue;
            }

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

    protected function generateCrudComponents($modelName, $fields)
    {
        $this->info("Generating CRUD components for {$modelName}...");

        // Generate Model
        $modelGenerator = new ModelGenerator;
        $modelGenerator->create($modelName, $fields);
        $this->line("Model created: {$modelName}");

        // Generate Requests
        $requests = new RequestsGenerator;
        $requests->create($modelName, $fields);
        $this->line("Requests created for {$modelName}");

        // Generate Resource
        $resource = new ResourceGenerator;
        $resource->create($modelName, $fields);
        $this->line("Resource created for {$modelName}");

        // Generate Controller
        $controller = new ControllerGenerator;
        $controller->create($modelName, $fields);
        $this->line("Controller created for {$modelName}");

        // Generate Migration
        $migrationGenerator = new MigrationGenerator;
        $migrationFilename = $migrationGenerator->create($modelName, $fields);
        $this->line("Migration created: {$migrationFilename}");
    }
}
