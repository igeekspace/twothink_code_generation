<?php

namespace igeekspace\TwothinkCodeGeneration\maker;

use igeekspace\TwothinkCodeGeneration\command\CodeGeneration;

class DetailViewMaker extends ViewMaker
{
    protected $controller = '';
    protected $configs = [];
    protected $type = 'detail';

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

        $genFields = $this->getGenFields();
        foreach ($genFields as &$genField) {
            $genField['name'] = $this->getFieldName($genField['key']);
            $genField['type'] = $this->getFieldType($genField['key']);
        }

        $template = $this->twig->load('detailView.twig');
        $templateCode = $template->render([
            'name' => $this->configs['name'],
            'configs' => $this->configs,
            'columns' => $genFields,
            'flController' => lcfirst($this->controller),
            'primaryKey' => $this->getPrimaryKey(),
        ]);

        if (!is_dir(dirname($pathname))) {
            mkdir(dirname($pathname), 0755, true);
        }

        file_put_contents($pathname, $templateCode);

        return true;
    }
}