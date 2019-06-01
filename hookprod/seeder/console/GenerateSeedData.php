<?php namespace Hookprod\Seeder\Console;

use Backend\Facades\BackendAuth;
use Backend\Models\User;
use Hookprod\Seeder\Classes\GenerateSeed;
use Hookprod\Seeder\Models\PluginModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

class GenerateSeedData extends Command
{
    /**
     * @var string The console command name.
     */
    protected $name = 'seed:generate';

    /**
     * @var string The console command description.
     */
    protected $description = 'Create seed for sync common tables';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        $plugins = PluginModel::getModels();
        foreach ($plugins as $models) {
            if(is_array($models)) {
                foreach ($models as $modelPath) {
                    Queue::push(GenerateSeed::class, $modelPath);
                }
            }
        }
        $this->info('Задачи для создания Seed\'s поставлены в очередь');
    }

}
