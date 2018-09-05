<?php
/**
 * Created by PhpStorm.
 * User: 57458
 * Date: 2018/8/15
 * Time: 14:21
 */

namespace App\Modules\Admin;


use App\BaseService;
use App\Exceptions\BaseResponseException;
use App\Exceptions\DataNotFoundException;
use App\Exceptions\NoPermissionException;
use Illuminate\Contracts\Support\Arrayable;

class AdminRuleService extends BaseService
{
    /**
     * 从权限树中获取权限列表
     * @param $tree
     * @param $list
     * @return array
     */
    private static function getRuleListFromTree($tree, &$list)
    {
        foreach ($tree as &$item) {
            $list[] = &$item;
            if(isset($item['sub']) && is_array($item['sub']) && sizeof($item['sub']) > 0){
                self::getRuleListFromTree($item['sub'], $list);
            }
        }
        return $list;
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
        $level1 = [];
        $level2 = [];
        $level3 = [];
        foreach ($rules as $r) {

            switch ($r['level']) {
                case 1:
                    $level1[$r['id']] = $r;
                    break;
                case 2:
                    $level2[$r['id']] = $r;
                    break;
                case 3:
                    $level3[$r['id']] = $r;
                    break;
                default:
                    break;
            }
        }

        if ($level3) {
            foreach ($level3 as $l) {
                $l['ppid'] = $level2[$l['pid']]['pid'];
                $level2[$l['pid']]['sub'][] = $l;
            }
        }

        if ($level2) {
            foreach ($level2 as $l) {
                $l['ppid'] = $level1[$l['pid']]['pid'];
                $level1[$l['pid']]['sub'][] = $l;
            }
        }

        foreach ($level1 as $l) {
            $l['ppid'] = 0;
            $tree[] = $l;
        }
        return $tree;

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

    /**
     * 递归获取无限极菜单栏
     * Author：  Jerry
     * Date：    180905
     * @param $rules
     * @return array
     */
    public static function convertRulesToTreeByRecursion( $rules )
    {
        foreach ($rules as $k=> $rule) {
            $rules[$k] = $rule->toArray();
        }
        return self::recursion( $rules);
    }

    /**
     * 执行递归
     * Author：  Jerry
     * Date：    180905
     * @param array  $rules
     * @param int $parentId
     * @return array
     */
    public static function recursion( &$rules, $parentId=0 )
    {
        $tree = [];
        foreach ($rules as $k=>$v){
            if($v['pid']==$parentId) {
                unset($rules[$k]);
                $temp = self::recursion($rules, $v['id']);
                if (!empty($temp))
                {
                    $v['sub'] = $temp;
                }
                unset($temp);
                $tree[] = $v;
            }
        }
        return $tree;
    }

    public static function getById($id)
    {
        return AdminAuthRule::find($id);
    }

    /**
     * 获取全部权限列表
     */
    public static function getAll()
    {
        return AdminAuthRule::orderBy('sort')->get();
    }

    /**
     * 获取树形结构的权限列表
     * @return array
     */
    public static function getTree()
    {
        $rules = self::getAll();
        // 封装权限列表, 子权限要在父权限的后面
        $tree = self::convertRulesToTree($rules);
        return $tree;
    }

    /**
     * 获取权限列表, 并根据权限层级排序, 即将权限树转换成列表
     * @return array
     */
    public static function getTreeList()
    {
        // 封装权限列表, 子权限要在父权限的后面
        $tree = self::getTree();
        $list = [];
        $list = self::getRuleListFromTree($tree, $list);
        return $list;
    }

    /**
     * 添加权限
     * @return AdminAuthRule
     */
    public static function addFromRequest()
    {
        $rule = new AdminAuthRule();
        $pid = request('pid', 0);
        if ($pid !=0 ) {
            $parent = AdminAuthRule::findOrFail($pid);
            if ($parent->level > 2) {
                throw new BaseResponseException('权限最多增加到3级');
            }
        }

        $rule->name = request('name', '');
        $rule->pid = $pid;
        $rule->level = $rule->pid == 0 ? 1 : $parent->level + 1;
        $rule->url = request('url', '');
        $rule->url_all = request('url_all', '');
        $rule->status = request('status', 1);
        $rule->sort = request('sort', 1);
        $rule->save();
        return $rule;
    }

    /**
     * 编辑权限信息
     * @param $id
     * @return AdminAuthRule
     */
    public static function editFromRequest($id)
    {
        $rule = self::getById($id);
        if(empty($rule)){
            throw new DataNotFoundException('权限信息不存在');
        }

        $pid = request('pid', 0);
        if ($pid !=0 ) {
            $parent = AdminAuthRule::findOrFail($pid);
            if ($parent->level > 2) {
                throw new BaseResponseException('权限最多增加到3级');
            }
        }

        $rule->name = request('name', '');
        $rule->pid = $pid;
        $rule->url = request('url', '');
        $rule->url_all = request('url_all', '');
        $rule->status = request('status', 1);
        $rule->sort = request('sort', 1);
        $rule->save();

        return $rule;
    }

    /**
     * 改变权限状态
     * @param $id
     * @param int $status
     * @return AdminAuthRule
     */
    public static function changeStatus($id, $status)
    {
        $rule = self::getById($id);
        if(empty($rule)){
            throw new DataNotFoundException('权限信息不存在');
        }

        $rule->status = $status;
        $rule->save();
        return $rule;
    }

    /**
     * @param $id
     * @return AdminAuthRule
     * @throws \Exception
     */
    public static function del($id)
    {
        $rule = self::getById($id);
        if(empty($rule)){
            throw new DataNotFoundException('权限信息不存在');
        }

        if(empty($rule->created_at)){
            throw new NoPermissionException('该权限不能删除');
        }

        $rule->delete();

        return $rule;
    }
}