<?php

namespace igeekspace\TwothinkCodeGeneration\command;

use igeekspace\TwothinkCodeGeneration\CodeGenerationModel;
use think\console\Input;
use think\console\Output;
use think\Db;

class Push extends CodeGeneration
{
    protected function configure()
    {
        $this->setName('code:push')
            ->setDescription('Push the local setting to the database');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln("start push");

        if (!is_dir(self::$configPath)) {
            $output->writeln("Can't find any config file, please run the pull command first or create the files yourself!");

            return -1;
        }

        foreach (scandir(self::$configPath) as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            $configs = require self::$configPath . $file;

            foreach ($configs['fields'] as $fieldConfig) {
                $nullStr = $fieldConfig['Null'] == 'YES' ? ' NULL' : ' NOT NULL';

                if (isset($fieldConfig['CodeGenerationConfigs']['dataSource']['model'])) {
                    $fieldConfig['CodeGenerationConfigs']['dataSource']['model'] = str_replace('\\',
                        '\\\\',
                        $fieldConfig['CodeGenerationConfigs']['dataSource']['model']);
                }
                $sql = sprintf("alter table %s modify column %s %s %s %s %s comment '%s';",
                    $configs['table_name'], $fieldConfig['Field'],
                    $fieldConfig['Type'], $nullStr,
                    $fieldConfig['Default'] !== null ? 'default ' . var_export($fieldConfig['Default'],
                            true) : '',
                    $fieldConfig['Extra'],
                    $fieldConfig['Comment']);

                Db::query($sql);
            }

            if (isset($configs['remark'])) {
                $tableComment = $configs['remark'];
            } else {
                $tableComment = $configs['name'];
            }

            Db::query('alter table ' . $configs['table_name'] . " comment '" . $tableComment . "';");

            $codeGenerationModel = new CodeGenerationModel();
            $codeGenConfigs = $codeGenerationModel
                ->where('table_name', $configs['table_name'])
                ->find();
            if ($codeGenConfigs) {
                $codeGenConfigs->configs = json_encode($configs,
                    JSON_UNESCAPED_UNICODE);
                $codeGenConfigs->save();
            } else {
                $codeGenerationModel->data([
                    'table_name' => $configs['table_name'],
                    'configs' => json_encode($configs,
                        JSON_UNESCAPED_UNICODE)
                ]);
                $codeGenerationModel->save();
            }
        }

        $output->writeln("Push successful");

        return 0;
    }
}