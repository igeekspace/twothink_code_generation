<?php

namespace igeekspace\TwothinkCodeGeneration\maker;

use igeekspace\TwothinkCodeGeneration\command\CodeGeneration;
use Riimu\Kit\PHPEncoder\PHPEncoder;

class AddViewMaker extends ViewMaker
{
    private $loadDatePicker = false;
    protected $controller = '';
    protected $configs = [];
    protected $type = 'add';

    public function __construct($configs)
    {
        parent::__construct();
        $this->configs = $configs;
        $this->controller = CodeGeneration::getControllerName($configs['table_name']);
    }

    public function makeView()
    {
        if (!isset($this->configs['admin'][$this->type]) || !$this->configs['admin'][$this->type]['generate_code']) {
            return false;
        }

        $pathname = $this->getGenFilePath();

        if (is_file($pathname)) {
            return false;
        }

        $template = $this->twig->load('addView.twig');

        $encoder = new PHPEncoder();
        $genFields = $this->getGenFields();
        foreach ($genFields as &$genField) {
            $genField['name'] = $this->getFieldName($genField['key']);
            $genField['type'] = $this->getFieldType($genField['key']);
            $genField['fieldConfigs'] = $this->getFieldConfigs($genField['key']);

            if ($genField['type'] == 'datetime') {
                $this->loadDatePicker = true;
            } elseif ($genField['type'] == 'select') {
                $genField['fieldConfigs']['dataSource']['condition'] = $encoder->encode($genField['fieldConfigs']['dataSource']['condition'],
                    ['string.escape' => false]);
                $genField['fieldConfigs']['dataSource']['fields'] = $encoder->encode($genField['fieldConfigs']['dataSource']['fields'],
                    ['string.escape' => false]);
            }
        }

        $templateCode = $template->render([
            'name' => $this->configs['name'],
            'configs' => $this->configs,
            'columns' => $genFields,
            'loadDatePicker' => $this->loadDatePicker,
        ]);

        if (!is_dir(dirname($pathname))) {
            mkdir(dirname($pathname), 0755, true);
        }

        file_put_contents($pathname, $templateCode);

        return true;
    }
}