<?php

namespace igeekspace\TwothinkCodeGeneration\command;

use igeekspace\TwothinkCodeGeneration\CodeGenerationModel;
use Riimu\Kit\PHPEncoder\PHPEncoder;
use think\console\Input;
use think\console\Output;
use think\Db;

class Pull extends CodeGeneration
{
    protected function configure()
    {
        $this->setName('code:pull')
            ->setDescription('Pull the settings from the database, and generate the config files');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln("start generate config files");

        $codeGenerationModel = new CodeGenerationModel();
        $codeGenConfigs = $codeGenerationModel->select();

        foreach ($codeGenConfigs as $codeGenConfig) {
            $filePath = self::$configPath . $codeGenConfig['table_name'] . '.php';
            if (is_file($filePath)) {
                continue;
            }

            $configs = json_decode($codeGenConfig['configs'], true);

            $configs['table_name'] = $codeGenConfig['table_name'];

            if (!isset($configs['admin'])) {
                $configs['admin'] = [];
                $configs['admin']['index'] = [
                    'generate_code' => true,
                ];
                $configs['admin']['add'] = [
                    'generate_code' => true,
                ];
                $configs['admin']['edit'] = [
                    'generate_code' => true,
                ];
                $configs['admin']['detail'] = [
                    'generate_code' => true,
                ];
                $configs['admin']['delete'] = [
                    'generate_code' => true,
                ];
            }

            $fields = Db::query("SHOW FULL COLUMNS FROM " . $configs['table_name']);
            $configs['fields'] = [];

            foreach ($fields as $field) {
                $fieldConfigs = json_decode($field['Comment'], true);

                if (!isset($fieldConfigs['name'])) {
                    $fieldConfigs = $field['Comment'] = [
                        'name' => $field['Comment'],
                        'remark' => $field['Comment']
                    ];
                }

                if (!isset($fieldConfigs['add'])
                    && $field['Field'] != 'id'
                    && $field['Field'] != 'operator_id'
                    && $field['Field'] != 'created_at'
                    && $field['Field'] != 'updated_at'
                    && $field['Field'] != 'deleted_at') {
                    $fieldConfigs['add'] = [
                        'generate_code' => true
                    ];
                }

                if (!isset($fieldConfigs['edit'])
                    && $field['Field'] != 'id'
                    && $field['Field'] != 'operator_id'
                    && $field['Field'] != 'created_at'
                    && $field['Field'] != 'updated_at'
                    && $field['Field'] != 'deleted_at') {
                    $fieldConfigs['edit'] = [
                        'generate_code' => true
                    ];
                }

                if (!isset($fieldConfigs['detail']) && $field['Field'] != 'deleted_at') {
                    $fieldConfigs['detail'] = [
                        'generate_code' => true
                    ];
                }

                $field['Comment'] = $fieldConfigs;

                $configs['fields'][$field['Field']] = $field;
            }

            if (!is_dir(self::$configPath)) {
                mkdir(self::$configPath, 0777, true);
            }

            $loader = new \Twig_Loader_Filesystem(self::$templatePath);
            $twig = new \Twig_Environment($loader);
            $template = $twig->load('config.twig');

            $encoder = new PHPEncoder();
            $code = $template->render([
                'configs' => $encoder->encode($configs,
                    ['string.escape' => false])
            ]);

            file_put_contents($filePath, $code);
        }

        $output->writeln("finish generate config files");

        return 0;
    }
}