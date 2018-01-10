<?php

namespace app\admin\logic;
use app\admin\model\ActionModel;
class AdminRoleLogic extends Logic{
    static public function getPrivilege()
    {
        $ActionModel = new ActionModel();
        $controllers = $ActionModel->where('function', '*')->select();
        $moudleArr = [];

        foreach ($controllers as $controller){
            $moudleArr[$controller['code']]['text'] = $controller['name'];
            $functions = $ActionModel->where('controller',$controller['controller'])
                ->where(['function'=>['neq','*']])->select();

            //降维
            $functionArr = [];
            foreach ($functions as $function){

                $functionArr[$function['code']] = $function['name'];
            }
            $moudleArr[$controller['code']]['child'] = $functionArr;
        }


        return $moudleArr;
    }

}