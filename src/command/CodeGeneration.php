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

    /**
     * Converts array() to []
     * @param  string
     * @return string
     */
    public static function convertArraysToSquareBrackets($code)
    {
        $out = '';
        $brackets = [];
        $tokens = token_get_all($code);
        for ($i = 0; $i < count($tokens); $i++) {
            $token = $tokens[$i];
            if ($token === '(') {
                $brackets[] = false;
            } elseif ($token === ')') {
                $token = array_pop($brackets) ? ']' : ')';
            } elseif (is_array($token) && $token[0] === T_ARRAY) {
                $a = $i + 1;
                if (isset($tokens[$a]) && $tokens[$a][0] === T_WHITESPACE) {
                    $a++;
                }
                if (isset($tokens[$a]) && $tokens[$a] === '(') {
                    $i = $a;
                    $brackets[] = true;
                    $token = '[';
                }
            }
            $out .= is_array($token) ? $token[1] : $token;
        }

        return $out;
    }
}