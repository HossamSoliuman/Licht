<?php

namespace Hossam\Licht\Console\Commands;

use Hossam\Licht\Generators\ControllerGeneratore;
use Illuminate\Console\Command;

class CrudGenerator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'licht:model {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'generating CRUDs';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $isThereMore = 'yes';
        $fields = [];
        while ($isThereMore == 'yes') {
            $fieldType = $this->choice(
                'Choose field type ',
                ['string', 'integer', 'text', 'foreignId'],
                0
            );
            $fieldName = $this->ask('Enter field name', 'name');

            $fields[$fieldName] = $fieldType;
            $isThereMore = $this->choice(
                'Are there any more fields?',
                ['yes', 'no'],
                0
            );
        }
        $model = $this->argument('name');

        $controller = new ControllerGeneratore;
        $controller->create($model);

        return Command::SUCCESS;
    }
}
