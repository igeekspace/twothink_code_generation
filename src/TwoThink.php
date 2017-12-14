<?php

namespace igeekspace\TwothinkCodeGeneration;

use app\admin\model\ActionModel;
use app\admin\model\AdminRoleModel;
use app\admin\model\MenuModel;

class TwoThink
{
    public function addActions($name, $controller)
    {
        $actions = [
            ['name' => $name . '管理', 'function' => '*'],
            ['name' => '查看' . $name . '列表', 'function' => 'index'],
            ['name' => '添加' . $name, 'function' => 'add'],
            ['name' => '编辑' . $name, 'function' => 'edit'],
            ['name' => '删除' . $name, 'function' => 'delete'],
            ['name' => '查看' . $name . '详情', 'function' => 'detail']
        ];

        foreach ($actions as $action) {
            $actionModel = new ActionModel();
            if ($actionModel->where('name', $action['name'])
                ->where('moudle', 'admin')
                ->where('controller', $controller)
                ->where('function', $action['function'])
                ->find()) {
                continue;
            }
            $actionModel->name = $action['name'];
            $actionModel->moudle = 'admin';
            $actionModel->controller = $controller;
            $actionModel->function = $action['function'];
            $actionModel->code = $actionModel->moudle . '.' . $actionModel->controller . '.' . $actionModel->function;
            $actionModel->remark = '';
            $actionModel->save();
            $this->addRolePerms(1, $actionModel->code);
        }
    }

    private function addRolePerms($roleId, $code)
    {
        $adminRoleModel = new AdminRoleModel();
        $adminRole = $adminRoleModel->find($roleId);

        $perms = $adminRole->perms;
        $permsArr = explode(',', $perms);

        if (!in_array($code, $permsArr)) {
            $permsArr[] = $code;
        }

        $adminRole->perms = implode(',', $permsArr);
        $adminRole->save();
    }

    public function addMenu($name, $controller, $pid = 0)
    {
        $menuModel = new MenuModel();
        if ($menuModel->where('title', $name)->find()) {
            return false;
        }

        $menuModel->title = $name;
        $menuModel->pid = $pid;
        $menuModel->url = '/admin/' . $controller . '/index';
        $menuModel->privilege_code = 'admin.' . $controller . '.index';
        $menuModel->icon = 'fa fa-cog';
        $menuModel->save();
    }
}