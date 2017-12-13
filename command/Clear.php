<?php

namespace igeekspace\CodeGeneration\command;

use igeekspace\CodeGeneration\maker\AddViewMaker;
use igeekspace\CodeGeneration\maker\ControllerMaker;
use igeekspace\CodeGeneration\maker\DetailViewMaker;
use igeekspace\CodeGeneration\maker\EditViewMaker;
use igeekspace\CodeGeneration\maker\IndexViewMaker;
use igeekspace\CodeGeneration\maker\ModelMaker;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;

class Clear extends CodeGeneration
{
    protected function configure()
    {
        $this->setName('code:clear')
            ->setDescription('Clear the generated files');
        $this->addArgument("tableNames", Argument::REQUIRED, 'table name');
    }

    protected function execute(Input $input, Output $output)
    {
        $tableNames = explode(',', $input->getArgument("tableNames"));

        foreach ($tableNames as $tableName) {
            $configs = self::getConfig($tableName);
            $module = 'common';

            $modelMaker = new ModelMaker();
            $modelMaker->removeModel($configs, $module);

            $controllerMaker = new ControllerMaker();
            $controllerMaker->removeController($configs);

            $addViewMaker = new AddViewMaker($configs);
            $addViewMaker->removeView();

            $editViewMaker = new EditViewMaker($configs);
            $editViewMaker->removeView();

            $indexViewMaker = new IndexViewMaker($configs);
            $indexViewMaker->removeView();

            $detailViewMaker = new DetailViewMaker($configs);
            $detailViewMaker->removeView();
        }

        return 0;
    }
}