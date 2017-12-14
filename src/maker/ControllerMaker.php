<?php

namespace igeekspace\TwothinkCodeGeneration\maker;

use igeekspace\TwothinkCodeGeneration\command\CodeGeneration;
use think\App;

class ControllerMaker extends Maker
{
    public function createController($configs, $module)
    {
        $controller = CodeGeneration::getControllerName($configs['table_name']);
        $template = $this->twig->load('controller.twig');

        $indexViewMaker = new IndexViewMaker($configs);
        $genFields = $indexViewMaker->getGenFields();

        $getters = [];
        foreach ($genFields as $genField) {
            if ($genField['key'] != $genField['getter']) {
                $getters[] = $genField['getter'];
            }
        }

        $controllerCode = $template->render([
            'module' => $module,
            'controller' => $controller,
            'flController' => lcfirst($controller),
            'name' => $configs['name'],
            'configs' => $configs,
            'getters' => $getters
        ]);

        $controllerClass = App::$namespace . '\\admin\\controller\\' . $controller;

        $pathname = $this->getPathName($controllerClass);

        if (is_file($pathname)) {
            return false;
        }

        if (!is_dir(dirname($pathname))) {
            mkdir(dirname($pathname), 0755, true);
        }

        file_put_contents($pathname, $controllerCode);

        return true;
    }

    public function removeController($configs)
    {
        $controller = CodeGeneration::getControllerName($configs['table_name']);
        $controllerClass = App::$namespace . '\\admin\\controller\\' . $controller;

        $pathname = $this->getPathName($controllerClass);

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