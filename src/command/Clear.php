<?php

namespace igeekspace\TwothinkCodeGeneration\command;

use igeekspace\TwothinkCodeGeneration\maker\AddViewMaker;
use igeekspace\TwothinkCodeGeneration\maker\ControllerMaker;
use igeekspace\TwothinkCodeGeneration\maker\DetailViewMaker;
use igeekspace\TwothinkCodeGeneration\maker\EditViewMaker;
use igeekspace\TwothinkCodeGeneration\maker\IndexViewMaker;
use igeekspace\TwothinkCodeGeneration\maker\ModelMaker;
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