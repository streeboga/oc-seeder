<?php namespace Hookprod\Seeder\Console;

use Backend\Facades\BackendAuth;
use Backend\Models\User;
use Hookprod\Seeder\Models\PluginModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SetSeedData extends Command
{
    /**
     * @var string The console command name.
     */
    protected $name = 'seed:set';

    /**
     * @var string The console command description.
     */
    protected $description = 'Save Seeds for sync common tables';

    /**
     * Execute the console command.
     * @return void
     */
    public function handle()
    {
        $allModels = [];
        $plugins = PluginModel::getModels();
        $times = PluginModel::getTimes();

        foreach ($plugins as $models) {
            if(is_array($models)) {
                foreach ($models as $modelPath) {
                    $model = new $modelPath();
                    $allModels[$model->getTable()] = isset($times[$model->getTable()]) ? $times[$model->getTable()] : null;
                    unset($model);
                }
            }
        }

        $files = Storage::files('seeder/DataSeeds');
        BackendAuth::login(User::first());
        foreach($files as $file) {
            $seed = json_decode(Storage::get($file), true);
            if(array_key_exists($seed['table'],$allModels)) {
                if ($allModels[$seed['table']] == null || $allModels[$seed['table']] < $seed['time']) {
                    $this->info('Обновление данных таблицы: '.$seed['table']);
                    $allModels[$seed['table']] = $seed['time'];
                    $modelPath = '\\' . $seed['model'];
                    $modelPath::truncate();
                    \Model::unguard();

                    $model = new $modelPath;
                    foreach ($seed['data'] as $item) {
                        \DB::connection(($model->getConnectionName() ?? config('database.default')))->table($seed['table'])->insert($item);
                    }

                    $conn = config('database.connections.'.($model->getConnectionName() ?? config('database.default')));
                    $primaryKey = ($model->getKeyName() ? $model->getKeyName() : 'id');
                    if($conn['driver'] == "pgsql") {
                        try {
                            \DB::statement("SELECT setval('{$model->getTable()}_{$primaryKey}_seq', coalesce(max(id),0) + 1, false) FROM {$conn['schema']}.{$model->getTable()};");
                        } catch (\PDOException $e) {
                            if($e->getCode() == '42P01') {
                                \DB::statement("CREATE SEQUENCE {$model->getTable()}_{$primaryKey}_seq START WITH 1 INCREMENT BY 1 NO MINVALUE NO MAXVALUE CACHE 1;");
                            }
                        }
                    }

                    \Model::reguard();
                    $this->info('Данные синхронизированы: '.$seed['table']);
                }
            }
        }

        PluginModel::setTimes($allModels);
    }

    /**
     * Get the console command arguments.
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * Get the console command options.
     * @return array
     */
    protected function getOptions()
    {
        return [];
    }

}
