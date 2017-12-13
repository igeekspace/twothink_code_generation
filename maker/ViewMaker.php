<?php

namespace igeekspace\CodeGeneration\maker;

use igeekspace\CodeGeneration\command\CodeGeneration;

abstract class ViewMaker extends Maker
{
    protected $configs = [];
    protected $controller = '';
    protected $type = '';

    protected function getFieldName($field)
    {
        $tableFields = $this->configs['fields'];

        if (isset($tableFields[$field])) {
            $fieldConfigs = $tableFields[$field]['Comment'];
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
        if (isset($tableFields[$field]['Comment'])) {
            $fieldConfigs = $tableFields[$field]['Comment'];

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

        $fieldConfigs = $tableFields[$field]['Comment'];

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
            if (isset($tableField['Comment'][$this->type]) && $tableField['Comment'][$this->type]['generate_code']) {
                $tableField['Comment'][$this->type]['key'] = $tableField['Field'];

                if (isset($tableField['Comment']['getter'])) {
                    $tableField['Comment'][$this->type]['getter'] = $tableField['Comment']['getter'];
                } else {
                    $tableField['Comment'][$this->type]['getter'] = $tableField['Comment'][$this->type]['key'];
                }
                $fields[$tableField['Field']] = $tableField['Comment'][$this->type];
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