<?php

namespace igeekspace\TwothinkCodeGeneration\maker;

abstract class ViewMaker extends Maker
{
    protected $configs = [];
    protected $controller = '';
    protected $type = '';

    protected function getFieldName($field)
    {
        $tableFields = $this->configs['fields'];

        if (isset($tableFields[$field])) {
            $fieldConfigs = $tableFields[$field]['CodeGenerationConfigs'];
            $name = $fieldConfigs['name'];
        } else {
            $getters = $this->configs['getters'];
            $name = $getters[$field]['name'];
        }

        return $name;
    }

    protected function getFieldType($field)
    {
        $tableFields = $this->configs['fields'];

        $type = 'text';
        if (isset($tableFields[$field]['CodeGenerationConfigs'])) {
            $fieldConfigs = $tableFields[$field]['CodeGenerationConfigs'];

            if (isset($fieldConfigs['type'])) {
                $type = $fieldConfigs['type'];
            }
        }

        return $type;
    }

    protected function getPrimaryKey()
    {
        $tableFields = $this->configs['fields'];

        $primaryKey = 'id';

        foreach ($tableFields as $tableField) {
            if ($tableField['Key'] == 'PRI') {
                $primaryKey = $tableField['Field'];
            }
        }

        return $primaryKey;
    }

    protected function getFieldConfigs($field)
    {
        $tableFields = $this->configs['fields'];

        $fieldConfigs = $tableFields[$field]['CodeGenerationConfigs'];

        return $fieldConfigs;
    }

    /**
     *  获取要生成代码的字段
     */
    public function getGenFields()
    {
        $tableFields = $this->configs['fields'];

        $fields = [];
        foreach ($tableFields as $tableField) {
            if (isset($tableField['CodeGenerationConfigs'][$this->type]) && $tableField['CodeGenerationConfigs'][$this->type]['generate_code']) {
                $tableField['CodeGenerationConfigs'][$this->type]['key'] = $tableField['Field'];

                if (isset($tableField['CodeGenerationConfigs']['getter'])) {
                    $tableField['CodeGenerationConfigs'][$this->type]['getter'] = $tableField['CodeGenerationConfigs']['getter'];
                } else {
                    $tableField['CodeGenerationConfigs'][$this->type]['getter'] = $tableField['CodeGenerationConfigs'][$this->type]['key'];
                }
                $fields[$tableField['Field']] = $tableField['CodeGenerationConfigs'][$this->type];
            }
        }

        return $fields;
    }

    /**
     *  获取生成的代码的保存文件路径
     */
    protected function getGenFilePath()
    {
        return APP_PATH . 'admin/view/' . $this->controller . "/{$this->type}.html";
    }

    public function removeView()
    {
        $pathname = $this->getGenFilePath();

        if (is_file($pathname)) {
            return unlink($pathname);
        }

        return true;
    }
}