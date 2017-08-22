<?php
/**
 * User: Evan Lee
 * Date: 2017/8/17
 * Time: 11:12
 */

namespace App\Modules\BossAuth;


use App\Modules\Service;
use Illuminate\Contracts\Support\Arrayable;

class BossAuthService extends Service
{

    /**
     * 检查用户是否有指定URL的权限
     * @param $userId int
     * @param $url string
     * @return bool
     */
    public static function checkAuthByUrl($userId, $url)
    {
        $rules = self::getEnableRulesByUserId($userId);

        $hasPermission = $rules->contains(function($rule, $index) use ($url){
            $ruleUrls = array_map('strtolower' , explode(',', $rule['url_all']));
            return in_array(strtolower($url), $ruleUrls);
        });
        return $hasPermission;
    }

    /**
     * 获取用户的权限列表
     * @param $userId
     * @return \Illuminate\Support\Collection
     */
    public static function getEnableRulesByUserId($userId)
    {
        $user = self::getUserById($userId);
        if($user->is_super){
            return self::getAllEnableRules();
        }
        $groupId = $user->group_id;
        return self::getEnableRulesByGroupId($groupId);
    }

    /**
     * @param $groupId
     * @return \Illuminate\Support\Collection
     */
    public static function getEnableRulesByGroupId($groupId)
    {
        $ruleIds = BossAuthGroup::where('id', $groupId)->value('rule_ids');
        $rules = BossAuthRule::whereIn('id', explode(',', $ruleIds))->get();
        return $rules;
    }

    /**
     * 获取用户的权限列表
     * @param $userId
     * @return \Illuminate\Support\Collection
     */
    public static function getRulesByUserId($userId)
    {
        $user = self::getUserById($userId);
        $groupId = $user->group_id;
        return self::getRulesByGroupId($groupId);
    }

    /**
     * @param $groupId
     * @return \Illuminate\Support\Collection
     */
    public static function getRulesByGroupId($groupId)
    {
        $ruleIds = BossAuthGroup::where('id', $groupId)->value('rule_ids');
        $rules = BossAuthRule::whereIn('id', explode(',', $ruleIds))->get();
        return $rules;
    }

    /**
     * 根据用户名获取用户信息
     * @param $username
     * @return BossUser
     */
    public static function getUserByName($username)
    {
        return BossUser::where('username', $username)->first();
    }

    /**
     * @param $userId
     * @return BossUser
     */
    public static function getUserById($userId)
    {
        return BossUser::find($userId);
    }

    /**
     * @param $groupId
     * @return BossAuthGroup
     */
    public static function getGroupById($groupId)
    {
        return BossAuthGroup::find($groupId);
    }

    /**
     * @param $ruleId
     * @return BossAuthRule
     */
    public static function getRuleById($ruleId)
    {
        return BossAuthRule::find($ruleId);
    }


    public static function getAllUsers()
    {
        return BossUser::all();
    }

    public static function getAllGroups()
    {
        return BossAuthGroup::all();
    }

    public static function getAllRules()
    {
        return BossAuthRule::all();
    }

    /**
     * 获取所有可用的权限
     * @return \Illuminate\Support\Collection
     */
    private static function getAllEnableRules()
    {
        return BossAuthRule::where('status', 1)->get();
    }

    /**
     * 生成加密后的密码
     * @param $password
     * @param $salt
     * @return string
     */
    public static function genPassword($password, $salt)
    {
        return md5(md5($password) . $salt);
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
                if(!isset($rule2['sub'])){
                    $rule2['sub'] = [];
                }
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