<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/8/15
 * Time: 14:12
 */

namespace App\Modules\Admin;


use App\BaseService;
use App\Exceptions\DataNotFoundException;

class AdminGroupService extends BaseService
{

    /**
     * 获取全部角色列表
     * @return AdminAuthGroup[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getAllGroups()
    {
        return AdminAuthGroup::all();
    }

    public static function getById($groupId)
    {
        return AdminAuthGroup::find($groupId);
    }

    /**
     * 添加角色
     * @param string $name
     * @param string $ruleIds
     * @param int $status
     * @return AdminAuthGroup
     */
    public static function add($name, $ruleIds = '', $status = 1)
    {
        if (is_string($ruleIds) && !empty($ruleIds)) {
            $ruleIds = explode(',',$ruleIds);
            $rules = AdminAuthRule::whereIn('id',$ruleIds)->get()->toArray();
            foreach ($rules as $r) {
                if ($r['pid'] != 0 && !in_array($r['pid'], $ruleIds)) {
                    $ruleIds[] = $r['pid'];
                }
            }

        }

        $ruleIds = implode(',', $ruleIds);

        $group = new AdminAuthGroup();
        $group->name = $name;
        $group->status = $status;
        $group->rule_ids = $ruleIds;
        $group->save();
        return $group;
    }

    /**
     * 编辑角色信息
     * @param int $id
     * @param string $name
     * @param string $ruleIds
     * @param int $status
     * @return AdminAuthGroup
     */
    public static function edit($id, $name, $ruleIds = '', $status = 1)
    {
        $group = self::getById($id);
        if(empty($group)){
            throw new DataNotFoundException('角色信息不存在');
        }

        if (is_string($ruleIds) && !empty($ruleIds)) {
            $ruleIds = explode(',',$ruleIds);
            $rules = AdminAuthRule::whereIn('id',$ruleIds)->get()->toArray();
            foreach ($rules as $r) {
                if ($r['pid'] != 0 && !in_array($r['pid'], $ruleIds)) {
                    $ruleIds[] = $r['pid'];
                }
            }

        }

        $ruleIds = implode(',', $ruleIds);

        $group->name = $name;
        $group->status = $status;
        $group->rule_ids = $ruleIds;
        $group->save();
        return $group;
    }

    /**
     * 改变角色状态
     * @param $id
     * @param $status
     * @return AdminAuthGroup
     */
    public static function changeStatus($id, $status)
    {
        $group = self::getById($id);
        if(empty($group)){
            throw new DataNotFoundException('角色信息不存在');
        }
        $group->status = $status;
        $group->save();
        return $group;
    }

    /**
     * 删除角色信息
     * @param $id
     * @return AdminAuthGroup
     * @throws \Exception
     */
    public static function del($id)
    {
        $group = self::getById($id);
        if(empty($group)){
            throw new DataNotFoundException('角色信息不存在');
        }
        $group->delete();
        return $group;
    }

}