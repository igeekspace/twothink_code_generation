<?php

namespace igeekspace\CodeGeneration\command;

use igeekspace\CodeGeneration\maker\AddViewMaker;
use igeekspace\CodeGeneration\maker\ControllerMaker;
use igeekspace\CodeGeneration\maker\DetailViewMaker;
use igeekspace\CodeGeneration\maker\EditViewMaker;
use igeekspace\CodeGeneration\maker\IndexViewMaker;
use igeekspace\CodeGeneration\maker\ModelMaker;
use igeekspace\CodeGeneration\TwoThink;
use think\App;
use think\console\Input;
use think\console\Output;

class Generate extends CodeGeneration
{
    protected function configure()
    {
        $this->setName('code:generate')
            ->setDescription('Generate the code');
    }

    protected function execute(Input $input, Output $output)
    {
        if (!is_dir(self::$configPath)) {
            $output->writeln("Can't find any config file, please run the pull command first!");

            return -1;
        }

        foreach (scandir(self::$configPath) as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            $configs = require self::$configPath . $file;

            if (!$configs['generate_code']) {
                continue;
            }

            $this->generateCode($configs, 'common', $output);
        }

        return 0;
    }

    private function generateCode(
        $configs,
        $module,
        Output $output
    ) {
        $name = $configs['name'];
        $controller = $this->getControllerName($configs['table_name']);
        $modelClass = App::$namespace . '\\' . $module . '\\model\\' . $controller . 'Model';

        if (!class_exists($modelClass)) {
            $output->writeln($controller . "Model is not exist,start to generate model code!");
            $modelMaker = new ModelMaker();

            if ($modelMaker->createModel($configs, $module)) {
                $output->writeln($controller . "Model code was generated successfully!");
            } else {
                $output->writeln($controller . "Model code was generated failed!");
            }
        } else {
            $output->writeln($controller . "Model is exist,skip!");
        }

        $controllerClass = App::$namespace . '\\admin\\controller\\' . $controller;
        if (!class_exists($controllerClass)) {
            $output->writeln($controller . " controller is not exist,start to generate controller code!");
            $controllerMaker = new ControllerMaker();

            if ($controllerMaker->createController($configs, $module)) {
                $output->writeln($controller . " controller code was generated successfully!");
            } else {
                $output->writeln($controller . " controller code was generated failed!");
            }
        } else {
            $output->writeln($controller . " controller is exist,skip!");
        }

        $indexViewMaker = new IndexViewMaker($configs);

        if ($indexViewMaker->makeView()) {
            $output->writeln($controller . " index view code was generated successfully!");
        } else {
            $output->writeln($controller . " index view was generated failed!");
        }

        $addViewMaker = new AddViewMaker($configs);
        if ($addViewMaker->makeView()) {
            $output->writeln($controller . " add view code was generated successfully!");
        } else {
            $output->writeln($controller . " add view was generated failed!");
        }

        $editViewMaker = new EditViewMaker($configs);
        if ($editViewMaker->makeView()) {
            $output->writeln($controller . " edit view code was generated successfully!");
        } else {
            $output->writeln($controller . " edit view was generated failed!");
        }

        $detailViewMaker = new DetailViewMaker($configs);
        if ($detailViewMaker->makeView()) {
            $output->writeln($controller . " detail view code was generated successfully!");
        } else {
            $output->writeln($controller . " detail view was generated failed!");
        }

        $twoThink = new TwoThink();
        $twoThink->addActions($name, $controller);
        $twoThink->addMenu($name . '管理', $controller, $configs['topMenu']);
        $output->writeln($controller . " finish add menus, add actions!!!");
    }
}