<?php

namespace Hossam\Licht\Console\Commands;

use Hossam\Licht\Generators\ControllerGeneratore;
use Illuminate\Console\Command;

class CrudGeneratore extends Command
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
            $fieledType = $this->choice(
                'choice field type ',
                ['string', 'integer', 'text', 'foreignId'],
                0
            );
            $fieledName = $this->ask('Enter field name', 'name');

            $fields[$fieledName] = $fieledType;
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
