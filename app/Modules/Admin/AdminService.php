<?php
/**
 * Created by PhpStorm.
 * User: Evan lee
 * Date: 2018/2/2
 * Time: 12:42
 */

namespace App\Modules\Admin;


use Illuminate\Contracts\Support\Arrayable;

class AdminService
{
    public static function getRulesForUser(AdminUser $user, $enable=true)
    {
        if($user->isSuper()){
            $rules = AdminAuthRule::when($enable, function($query){
                $query->where('status', AdminAuthRule::STATUS_ON);
            })->orderBy('sort')->get();
        }else {
            $ruleIds = AdminAuthGroup::where('id', $user->group_id)->value('rule_ids');
            $rules = AdminAuthRule::when($enable, function($query){
                $query->where('status', AdminAuthRule::STATUS_ON);
            })->whereIn('id', explode(',', $ruleIds))->orderBy('sort')->get();
        }
        return $rules;
    }

    /**
     * 将权限数组转换为树形结构
     * @param $rules array|Arrayable
     * @return array
     */
    public static function convertRulesToTree($rules){
        foreach ($rules as $k=> $rule) {
            $rules[$k] = $rule->toArray();
        }
        $tree = [];
        foreach ($rules as &$parent){
            if($parent['pid'] == 0){
                $tree[] =& $parent;
            }
            foreach ($rules as &$sub){
                if($sub['pid'] == $parent['id']){
                    if(!isset($parent['sub'])){
                        $parent['sub'] = [];
                    }
                    $parent['sub'][] =& $sub;
                }
            }

        }
        return $tree;
    }
}