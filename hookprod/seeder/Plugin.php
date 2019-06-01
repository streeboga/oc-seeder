<?php namespace Hookprod\Seeder;

use Backend\Facades\Backend;
use Hookprod\Seeder\Classes\GenerateSeed;
use Hookprod\Seeder\Console\GenerateSeedData;
use Hookprod\Seeder\Console\SetSeedData;
use Hookprod\Seeder\Models\PluginModel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use System\Classes\PluginBase;

class Plugin extends PluginBase
{

    public function boot()
    {
//        $plugins = PluginModel::getModels();
//        foreach ($plugins as $models) {
//            if(is_array($models)) {
//                foreach ($models as $modelPath) {
//                    $modelPath::extend(function ($model) use ($modelPath) {
//                        $model->bindEvent('model.afterSave', function() use ($model, $modelPath) {
//                            if(!$model->isUnguarded()) {
//                                Log::info('Добавление задачи на создание Seed в очередь! ');
//                                Queue::push(GenerateSeed::class, $modelPath);
//                            }
//                        });
//                        $model->bindEvent('model.afterDelete', function() use ($model, $modelPath) {
//                            if(!$model->isUnguarded()) {
//                                Log::info('Добавление задачи на создание Seed в очередь! ');
//                                Queue::push(GenerateSeed::class, $modelPath);
//                            }
//                        });
//                    });
//                }
//            }
//        }
    }

    public function registerSchedule($schedule)
    {
//        $schedule->command('queue:work --once')->everyMinute();
    }

    public function registerComponents()
    {
    }

    public function register()
    {
        $this->registerConsoleCommand('seed:generate', GenerateSeedData::class);
        $this->registerConsoleCommand('seed:set', SetSeedData::class);
    }

    public function registerSettings()
    {
        return [
            'seeder_settings'      => [
                'label'       => 'hookprod.seeder::lang.settings.models',
                'description' => 'hookprod.seeder::lang.settings.description',
                'category'    => 'hookprod.seeder::lang.settings.seeder',
                'icon'        => 'icon-compress',
                'url'         => Backend::url('hookprod/seeder/models'),
                'order'       => 50,
                'permissions' => ['seeder.*'],
                'keywords'    => 'dropdown dynamic options',
            ],
        ];
    }
}
