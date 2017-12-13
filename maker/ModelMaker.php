<?php

namespace igeekspace\CodeGeneration\maker;

use igeekspace\CodeGeneration\BaseModel;
use igeekspace\CodeGeneration\command\CodeGeneration;
use think\App;
use think\Loader;

class ModelMaker extends Maker
{
    public function createModel($configs, $module)
    {
        $controller = CodeGeneration::getControllerName($configs['table_name']);
        $table = config('database.prefix') . Loader::parseName($controller);
        $baseModel = new BaseModel();
        $baseModel->table($table);
        $fieldsType = $baseModel->getFieldsType();//获取各个字段的类型

        $template = $this->twig->load('model.twig');

        if (isset($configs['getters'])) {
            foreach ($configs['getters'] as $key => &$getter) {
                if ($getter['type'] == 2) {
                    if ($getter['dataSource']['type'] == 2) {
                        $foreignConfigs = [];

                        $foreignConfigs['model'] = $getter['dataSource']['model'];
                        $strs = explode('\\', $foreignConfigs['model']);
                        $foreignConfigs['controller'] = $strs[count($strs) - 1];
                        $foreignConfigs['flController'] = lcfirst($foreignConfigs['controller']);
                        $getter['foreign_configs'] = $foreignConfigs;
                    }

                    $getter['code'] = Loader::parseName($key, 1);
                }
            }
        }

        $modelCode = $template->render([
            'module' => $module,
            'controller' => $controller,
            'table' => $table,
            'fieldsType' => $fieldsType,
            'configs' => $configs,
        ]);

        $modelClass = App::$namespace . '\\' . $module . '\\model\\' . $controller . 'Model';
        $pathname = $this->getPathName($modelClass);

        if (is_file($pathname)) {
            return false;
        }

        if (!is_dir(dirname($pathname))) {
            mkdir(dirname($pathname), 0755, true);
        }

        file_put_contents($pathname, $modelCode);

        return true;
    }

    public function removeModel($configs, $module)
    {
        $controller = CodeGeneration::getControllerName($configs['table_name']);
        $modelClass = App::$namespace . '\\' . $module . '\\model\\' . $controller . 'Model';
        $pathname = $this->getPathName($modelClass);

        if (is_file($pathname)) {
            return unlink($pathname);
        }

        return true;
    }

    private function getPathName($name)
    {
        $name = str_replace(App::$namespace . '\\', '', $name);

        return APP_PATH . str_replace('\\', '/', $name) . '.php';
    }
}