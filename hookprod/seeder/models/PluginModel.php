<?php namespace Hookprod\Seeder\Models;

use Hookprod\Seeder\Classes\GenerateSeed;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Model;
use System\Classes\PluginManager;

/**
 * Model
 */
class PluginModel extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];
    protected $jsonable = ['options', 'models'];

    /**
     * @var string The database table used by the model.
     */
    public $table = '';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    protected $plugins;
    /**
     * Generates a seed class name (also used as a filename)
     * @param  string  $table
     * @param  string  $prefix
     * @param  string  $suffix
     * @return string
     */
    public static function generateClassName($table, $prefix=null, $suffix=null)
    {
        $tableString = '';
        $tableName = explode('_', $table);
        foreach ($tableName as $tableNameExploded) {
            $tableString .= ucfirst($tableNameExploded);
        }
        return ($prefix ? $prefix : '') . ucfirst($tableString) . 'Table' . ($suffix ? $suffix : '') . 'Seeder';
    }

    protected function getBackendPlugins()
    {
        $itemInfo = [
            'name' => 'CMS',
            'description' => null,
            'icon' => null,
            'path' => base_path().'/modules/cms',
            'code' => 'Cms',
            'namespace' => 'Cms',
        ];
        $itemInfo['full-text'] = trans($itemInfo['name']).' '.trans($itemInfo['description']);
        $result['October.Cms'] = $itemInfo;

        $itemInfo = [
            'name' => 'Backend',
            'description' => null,
            'icon' => null,
            'path' => base_path().'/modules/backend',
            'code' => 'Backend',
            'namespace' => 'Backend',
        ];
        $itemInfo['full-text'] = trans($itemInfo['name']).' '.trans($itemInfo['description']);
        $result['October.Backend'] = $itemInfo;

        $itemInfo = [
            'name' => 'System',
            'description' => null,
            'icon' => null,
            'path' => base_path().'/modules/system',
            'code' => 'System',
            'namespace' => 'System',
        ];
        $itemInfo['full-text'] = trans($itemInfo['name']).' '.trans($itemInfo['description']);
        $result['October.System'] = $itemInfo;


        return $result;
    }
    protected function getPluginList()
    {
        $plugins = PluginManager::instance()->getPlugins();

        $result = [];
        foreach ($plugins as $code=>$plugin) {
            $pluginInfo = $plugin->pluginDetails();
            $itemInfo = [
                'name' => isset($pluginInfo['name']) ? $pluginInfo['name'] : 'rainlab.builder::lang.plugin.no_name',
                'description' => isset($pluginInfo['description']) ? $pluginInfo['description'] : 'rainlab.builder::lang.plugin.no_description',
                'icon' => isset($pluginInfo['icon']) ? $pluginInfo['icon'] : null,
                'path' => PluginManager::instance()->getPluginPath($code),
                'code' => $code,
            ];

            list($namespace) = explode('\\', get_class($plugin));
            $itemInfo['namespace'] = trim($namespace);
            $itemInfo['full-text'] = trans($itemInfo['name']).' '.trans($itemInfo['description']);

            $result[$code] = $itemInfo;
        }

        uasort($result, function($a, $b) {
            return strcmp(trans($a['name']), trans($b['name']));
        });
        $result = array_merge($result, $this->getBackendPlugins());
//        dd($result);
        $this->plugins = $result;
        return $result;
    }

    public function getPluginOptions()
    {
        $plugins = $this->getPluginList();
        $data = [];
        foreach ($plugins as $code => $plugin) {
            $data[$code] = $code.' - '. e(trans($plugin['name']));
        }
        return $data;
    }

    public function findModelOptions($field = 'plugin', $value = null)
    {
        $plugins = $this->getPluginList();

        if ($value)
            $field = $value;

        if(isset($plugins[$field]) && is_dir($plugins[$field]['path'].'/models'))
            $models = File::files($plugins[$field]['path'].'/models');
        else
            $models = [];
        foreach($models as $model) {
            $title = str_replace('.php','', $model->getFilename()). '';
            $result[str_replace('.','\\', $plugins[$field]['code']).'\Models\\'.$title] =  $title;
        }

        return $result ?? [];
    }

    public function getModelOptions($fieldName = null, $keyValue = null)
    {
        return $this->findModelOptions('plugin', $keyValue->plugin);
    }

    public function getDropdownOptions($fieldName, $value, $formData)
    {
        return $this->findModelOptions($fieldName);
    }

    public static function getConfigPath() {
        return 'seeder/config.json';
    }
    public static function getTimesPath() {
        return 'seeder/times.json';
    }
    public static function getModels() {
        if(Storage::has(self::getConfigPath()))
            return json_decode(Storage::get(self::getConfigPath()), 1)['models'];
        return [];
    }
    public static function getTimes() {
        if(Storage::has(self::getTimesPath()))
            return json_decode(Storage::get(self::getTimesPath()), 1);
        return [];
    }
    public static function setTimes($times) {
        return Storage::put(self::getTimesPath(), json_encode($times, JSON_PRETTY_PRINT));
    }

    public static function init($plugins)
    {
        foreach ($plugins as $models) {
            if (is_array($models)) {
                foreach ($models as $modelPath) {
                    $model = new $modelPath();
                    $className = PluginModel::generateClassName($model->getTable());
                    if(!Storage::has('seeder/DataSeeds/'.$className.'.json')) {
                        Queue::push(GenerateSeed::class, $modelPath);
                    }
                }
            }
        }
    }
}
