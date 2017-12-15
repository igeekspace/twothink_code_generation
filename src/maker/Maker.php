<?php

namespace igeekspace\TwothinkCodeGeneration\maker;

use Riimu\Kit\PHPEncoder\PHPEncoder;
use think\Db;
use think\Loader;

class Maker
{
    protected $twig;

    public function __construct()
    {
        $loader = new \Twig_Loader_Filesystem(__DIR__ . '/../template');
        $this->twig = new \Twig_Environment($loader);

        $function = new \Twig_SimpleFunction('var_encode', function ($data) {
            $encoder = new PHPEncoder();

            return $encoder->encode($data, ['string.escape' => false]);
        });
        $this->twig->addFunction($function);
    }

    protected function getTable($controller)
    {
        return config('database.prefix') . Loader::parseName($controller);
    }

    protected function getTableFieldsByTable($table)
    {
        $fields = Db::query("SHOW FULL COLUMNS FROM " . $table);

        foreach ($fields as &$field) {
            if ($field['Field'] == 'id') {
                $field['Comment'] = 'id';
            } else if ($field['Field'] == 'created_at') {
                $field['Comment'] = '创建时间';
            } else if ($field['Field'] == 'updated_at') {
                $field['Comment'] = '最后修改时间';
            }
        }

        return $fields;
    }

    protected function getTableFileds($controller)
    {
        $table = $this->getTable($controller);

        return $this->getTableFieldsByTable($table);
    }
}