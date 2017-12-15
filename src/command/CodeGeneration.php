<?php

namespace igeekspace\TwothinkCodeGeneration\command;

use think\console\Command;
use think\Loader;

abstract class CodeGeneration extends Command
{
    protected static $configPath = DATA_PATH . 'code_gen/';
    protected static $templatePath = __DIR__ . '/../template';
    protected static $configContents = [];

    public static function getConfigPath()
    {
        return self::$configPath;
    }

    public static function getConfig($tableName)
    {
        if (isset(self::$configContents[$tableName])) {
            return self::$configContents[$tableName];
        }

        self::$configContents[$tableName] = include self::$configPath . $tableName . '.php';

        return self::$configContents[$tableName];
    }

    public static function getControllerName($tableFullName)
    {
        $tableName = str_replace(config('database.prefix'), '',
            $tableFullName);

        return Loader::parseName($tableName, 1);
    }
}