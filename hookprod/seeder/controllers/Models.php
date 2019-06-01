<?php namespace Hookprod\Seeder\Controllers;

use Backend\Classes\Controller;
use BackendMenu;
use Hookprod\Seeder\Models\PluginModel;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use October\Rain\Exception\ApplicationException;
use October\Rain\Support\Facades\Flash;
use System\Classes\SettingsManager;

class Models extends Controller
{
    public $requiredPermissions = [
        'seeder.*'
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('October.System', 'system', 'settings');
        SettingsManager::setContext('HookProd.Seeder', 'seeder_settings');
    }

    public function index()
    {
        try {
            $defaults = json_decode(Storage::get(PluginModel::getConfigPath()), 1);
            $this->pageTitle = 'Выберите модели';
        } catch (ApplicationException $e) {
            $this->handleError($e);
        }
        $config = $this->makeConfig();
        $config->model = new PluginModel();
        $config->fields['open'] = [
            "type" => "partial",
            "path" => "open_form",
        ];

        foreach($config->model->getPluginOptions() as $plugin => $title) {
            if(!empty($config->model->findModelOptions($plugin)))
                $config->fields[$plugin] = [
                    'span' => 'storm',
                    'cssClass' => 'col-md-2',
                    'label' => $title,
                    "type" => "checkboxlist",
                    "default" => $defaults['models'][str_replace('.','_', $plugin)] ?? [],
                ];
        }

        $config->fields['save'] = [
            "type" => "partial",
            "path" => "index",
        ];
        $widget = $this->makeFormWidget('Backend\Widgets\Form', $config);

        return '<form>'.$widget->render().'</form>';
    }

    public function onSave()
    {
        PluginModel::init(Request::all());

        Storage::put(PluginModel::getConfigPath(), json_encode(['models' => Request::all()], JSON_PRETTY_PRINT));
        Flash::success('Настройки успешно сохранены');
    }
}
