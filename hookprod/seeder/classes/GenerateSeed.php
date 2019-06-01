<?php
/**
 * Created by PhpStorm.
 * User: khook
 * Date: 30.05.19
 * Time: 12:58
 */

namespace Hookprod\Seeder\Classes;


use Hookprod\Seeder\Models\PluginModel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateSeed
{
    public function fire($job, $modelPath)
    {
        $all = $modelPath::all();
        $originals = [];
        foreach ($all as $item) {
            $originals[] = $item->getOriginal();
        }
        $model = new $modelPath;
        $className = PluginModel::generateClassName($model->getTable());

        Storage::put('seeder/DataSeeds/'.$className.'.json', json_encode( ['table' => $model->getTable(), 'model' => $modelPath, 'data' => $originals, 'time' => time()], JSON_PRETTY_PRINT));
        $job->delete();
    }

}
