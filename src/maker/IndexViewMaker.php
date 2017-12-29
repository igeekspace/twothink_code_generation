<?php

namespace igeekspace\TwothinkCodeGeneration\maker;

use igeekspace\TwothinkCodeGeneration\command\CodeGeneration;

/**
 * Class IndexViewMaker
 * @package igeekspace\TwothinkCodeGeneration\maker
 */
class IndexViewMaker extends ViewMaker
{
    protected $controller = '';
    protected $configs = [];
    protected $type = 'index';

    public function __construct($configs)
    {
        parent::__construct();
        $this->configs = $configs;
        $this->controller = CodeGeneration::getControllerName($configs['table_name']);
    }

    public function makeView()
    {
        if (!isset($this->configs['admin']['index'])) {
            return false;
        }

        $genFields = $this->getGenFields();
        foreach ($genFields as &$column) {
            $column['type'] = $this->getFieldType($column['key']);
        }

        $template = $this->twig->load('indexView.twig');
        $templateCode = $template->render([
            'ths' => $this->getThs(),
            'columns' => $genFields,
            'name' => $this->configs['name'],
            'configs' => $this->configs
        ]);

        $pathname = $this->getGenFilePath();
        if (is_file($pathname)) {
            return false;
        }

        if (!is_dir(dirname($pathname))) {
            mkdir(dirname($pathname), 0755, true);
        }

        file_put_contents($pathname, $templateCode);

        return true;
    }

    /**
     * 获取表头名
     * @return array
     */
    private function getThs()
    {
        $tableHeads = [];

        $genFields = $this->getGenFields();

        foreach ($genFields as $genField) {
            $field = $genField['getter'];
            $tableHeads[] = $this->getFieldName($field);
        }

        return $tableHeads;
    }
}